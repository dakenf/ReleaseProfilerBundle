<?php

namespace Daken\ReleaseProfilerBundle\Tests\EventSubscriber;

use Daken\ReleaseProfilerBundle\Entity\Error;
use Daken\ReleaseProfilerBundle\EventSubscriber\ProfilerEventSubscriber;
use Daken\ReleaseProfilerBundle\Logging\SQLLogger;
use Daken\ReleaseProfilerBundle\Notifier\NullNotifier;
use Daken\ReleaseProfilerBundle\Tests\PersistManager\TestPersistManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Tests\Fixtures\KernelForTest;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\User;

class ProfilerEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private $eventSubscriber;

    /**
     * @var SQLLogger
     */
    private $sqlLogger;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    private function getEventSubscriber()
    {
        if (!$this->eventSubscriber) {
            $logConditions = [
                'request' => [
                    ['exclude' => true, 'host' => 'excluded_host'],
                    ['always' => true]
                ],
                'request_body' => [['always' => true]],
                'response_body' => [['always' => true]],
            ];

            $this->tokenStorage = new TokenStorage();
            $this->tokenStorage->setToken(new AnonymousToken('123', 'anon.', ['ROLE_USER']));
            $this->sqlLogger = new SQLLogger(50);
            $this->sqlLogger->setOldLogger(new SQLLogger());


            $router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
            $router
                ->method('generate')
                ->willReturnCallback(function () {
                    return 'http://site.com/error';
                });

            $this->eventSubscriber = new ProfilerEventSubscriber(
                new TestPersistManager(),
                $this->sqlLogger,
                $router,
                null,
                $logConditions,
                $this->tokenStorage,
                new NullNotifier()
            );
        }

        return $this->eventSubscriber;
    }

    private function createSymfonyRequest()
    {
        $req = SymfonyRequest::create(
            'http://test.com/some_uri?param1=value1&param2=value2',
            'GET',
            [],
            [],
            [],
            [],
            '{ "bodyParam1": "bodyValue1" }'
        );

        return $req;
    }

    private function createSymfonyResponse()
    {
        $response = new JsonResponse(['resultValue' => 1]);
        return $response;
    }

    private function getKernel()
    {
        return new KernelForTest('dev', false);
    }

    public function testGetSubscribedEvents()
    {
        $events = ProfilerEventSubscriber::getSubscribedEvents();

        $subscriber = $this->getEventSubscriber();

        foreach ($events as $event) {
            foreach ($event as $functions) {
                $this->assertTrue(method_exists($subscriber, $functions[0]));
            }
        }
    }

    public function testOnKernelRequest()
    {
        $symfonyRequest = $this->createSymfonyRequest();
        $subscriber = $this->getEventSubscriber();

        $event = new GetResponseEvent($this->getKernel(), $symfonyRequest, HttpKernelInterface::SUB_REQUEST);
        $subscriber->onKernelRequest($event);
        $this->assertNull($subscriber->getRequest());

        $event = new GetResponseEvent($this->getKernel(), $symfonyRequest, HttpKernelInterface::MASTER_REQUEST);
        $subscriber->onKernelRequest($event);

        $profilerRequest = $subscriber->getRequest();

        $this->assertEquals($profilerRequest->getClientIp(), $symfonyRequest->getClientIp());
        $this->assertEquals($profilerRequest->getHost(), $symfonyRequest->getHost());
        $this->assertEquals($profilerRequest->getPath(), $symfonyRequest->getPathInfo());
        $this->assertEquals($profilerRequest->getQuery(), $symfonyRequest->getQueryString());
        $this->assertEquals($profilerRequest->getScheme(), $symfonyRequest->getScheme());
        $this->assertEquals($profilerRequest->getUserAgent(), $symfonyRequest->headers->get('User-Agent'));
        $this->assertEquals($profilerRequest->getRequestBody(), $symfonyRequest->getContent());
        $this->assertEquals($profilerRequest->getRequestMethod(), $symfonyRequest->getMethod());
    }

    public function testOnKernelResponse()
    {
        $symfonyRequest = $this->createSymfonyRequest();
        $response = $this->createSymfonyResponse();
        $subscriber = $this->getEventSubscriber();

        $event = new FilterResponseEvent(
            $this->getKernel(),
            $symfonyRequest,
            HttpKernelInterface::SUB_REQUEST,
            $response
        );
        $subscriber->onKernelResponse($event);
        $this->assertNotEquals($response->getContent(), $subscriber->getRequest());

        $event = new FilterResponseEvent(
            $this->getKernel(),
            $symfonyRequest,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $subscriber->onKernelResponse($event);

        $profilerRequest = $subscriber->getRequest();

        $this->assertEquals($response->getContent(), $profilerRequest->getResponse());
        $this->assertEquals($response->getStatusCode(), $profilerRequest->getResponseCode());
    }

    public function testOnException()
    {
        $symfonyRequest = $this->createSymfonyRequest();
        $response = $this->createSymfonyResponse();

        $exception = new NotFoundHttpException("Not found");
        $event = new GetResponseForExceptionEvent(
            $this->getKernel(),
            $symfonyRequest,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $event->setResponse($response);
        $subscriber = $this->getEventSubscriber();
        $subscriber->onException($event);

        $profilerRequest = $subscriber->getRequest();

        $this->assertGreaterThan(0, count($profilerRequest->getErrors()));
        $errors = $profilerRequest->getErrors();
        /** @var Error $error */
        $error = $errors[0];

        $this->assertEquals($error->getExceptionClass(), get_class($exception));
        $this->assertNotNull($error->getStackTrace());
        $this->assertGreaterThan(0, $error->getLineNumber());
        $this->assertNotNull($error->getFilename());
        $this->assertNotNull($error->getError());
        $this->assertEquals($profilerRequest->getResponseCode(), 404);
        $this->assertEquals($profilerRequest->getResponse(), $response->getContent());
    }

    public function testShouldLog()
    {
        $config = [
            ['exclude' => true, 'host' => 'exclude.host.com'],
            ['error' => true],
            ['exclude' => true, 'path_preg' => '#^/admin/#'],
            ['route' => 'test_route']
        ];

        $subscriber = $this->getEventSubscriber();
        $symfonyRequest = $this->createSymfonyRequest();
        $event = new GetResponseEvent($this->getKernel(), $symfonyRequest, HttpKernelInterface::MASTER_REQUEST);
        $subscriber->onKernelRequest($event);

        $request = $subscriber->getRequest();
        $request->setHost('exclude.host.com');
        $this->assertFalse($subscriber->shouldLog($config));
        $request->setHost('host.com');

        $error = new Error();
        $exception = new NotFoundHttpException();
        $error->setExceptionClass(get_class($exception));
        $request->addError($error);
        $this->assertTrue($subscriber->shouldLog($config));

        $request->removeError($error);

        $request->setPath('/admin');
        $this->assertFalse($subscriber->shouldLog($config));
        $request->setPath('/1/admin');

        $request->setMatchedRoute('test_route');
        $this->assertTrue($subscriber->shouldLog($config));
        $request->setMatchedRoute('test_route1');

        $this->assertFalse($subscriber->shouldLog($config));

        $config = [
            ['error' => get_class($exception)],
        ];
        $request->addError($error);
        $this->assertTrue($subscriber->shouldLog($config));

        $error->setExceptionClass('asd');
        $this->assertFalse($subscriber->shouldLog($config));
    }

    public function testOnKernelTerminate()
    {
        $symfonyRequest = $this->createSymfonyRequest();
        //$response = $this->createSymfonyResponse();

        $subscriber = $this->getEventSubscriber();

        $event = new GetResponseEvent($this->getKernel(), $symfonyRequest, HttpKernelInterface::MASTER_REQUEST);
        $subscriber->onKernelRequest($event);

        $exampleQuery = "SELECT *";
        $queryParams = ['123'];
        $this->sqlLogger->startQuery($exampleQuery, $queryParams);
        usleep(110000);
        $this->sqlLogger->stopQuery();

        // this query should not be logged
        $this->sqlLogger->startQuery($exampleQuery);
        $this->sqlLogger->stopQuery();

        //$event = new PostResponseEvent($this->getKernel(), $symfonyRequest, $response);

        $subscriber->onKernelTerminate();

        $profilerRequest = $subscriber->getRequest();

        $this->assertEquals($profilerRequest->getUsername(), 'anon.');
        $this->assertEquals($profilerRequest->getTotalDatabaseQueryCount(), 2);
        $this->assertGreaterThan(100, $profilerRequest->getTotalDatabaseQueryTime());

        $queries = $profilerRequest->getDatabaseQueries();
        $this->assertEquals(count($queries), 1);
        $query = $queries[0];

        $this->assertEquals($query->getQuery(), $exampleQuery);
        $this->assertEquals($query->getParameters(), json_encode($queryParams));
        $this->assertGreaterThan(100, $query->getTime());
        $this->assertNotNull($query->getStackTrace());


        $user = new User('username', 'password');
        $this->tokenStorage->setToken(new PreAuthenticatedToken(
            $user,
            '123',
            '123'
        ));
        $subscriber->onKernelTerminate();

        $profilerRequest->setHost('excluded_host');
        $subscriber->onKernelTerminate();
    }
}
