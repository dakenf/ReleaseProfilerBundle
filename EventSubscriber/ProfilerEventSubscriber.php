<?php

namespace Daken\ReleaseProfilerBundle\EventSubscriber;

use Daken\ReleaseProfilerBundle\Entity\Error;
use Daken\ReleaseProfilerBundle\Entity\Request;
use Daken\ReleaseProfilerBundle\Notifier\NotifierInterface;
use Daken\ReleaseProfilerBundle\Logging\SQLLogger;
use Daken\ReleaseProfilerBundle\PersistManager\PersistManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilerEventSubscriber implements EventSubscriberInterface
{
    /** @var  Request */
    private $request;
    private $persistManager;
    private $sqlLogger;
    private $stopwatch;
    private $logConditions;
    private $tokenStorage;
    private $errorNotifier;
    private $router;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0]
            ],
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -255]
            ],
            KernelEvents::EXCEPTION => [
                ['onException', 0]
            ],
            KernelEvents::TERMINATE => [
                ['onKernelTerminate', -255]
            ],
        ];
    }

    public function __construct(
        PersistManagerInterface $pm,
        SQLLogger $logger,
        RouterInterface $router,
        Stopwatch $stopwatch = null,
        array $logConditions = array(),
        TokenStorageInterface $tokenStorage = null,
        NotifierInterface $errorNotifier = null
    ) {
        $this->persistManager = $pm;
        $this->sqlLogger = $logger;
        $this->stopwatch = $stopwatch;
        $this->logConditions = $logConditions;
        $this->tokenStorage = $tokenStorage;
        $this->errorNotifier = $errorNotifier;
        $this->router = $router;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $this->request = Request::fromHttpRequest($event->getRequest());

        if ($this->shouldLog($this->logConditions['request_body'])) {
            $this->request->setRequestBody($event->getRequest()->getContent());
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if (!$this->request) {
            $this->request = Request::fromHttpRequest($event->getRequest());
        }

        $this->request->setResponseCode($event->getResponse()->getStatusCode());
        $this->request->setMatchedRoute($event->getRequest()->get('_route'));
        if ($this->shouldLog($this->logConditions['response_body'])) {
            $this->request->setResponse($event->getResponse()->getContent());
        }
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        if (!$this->request) {
            $this->request = Request::fromHttpRequest($event->getRequest());
            if ($this->shouldLog($this->logConditions['request_body'])) {
                $this->request->setRequestBody($event->getRequest()->getContent());
            }
        }

        $this->request->setMatchedRoute($event->getRequest()->get('_route'));

        if ($event->getResponse()) {
            $this->request->setResponseCode($event->getResponse()->getStatusCode());
            if ($this->shouldLog($this->logConditions['response_body'])) {
                $this->request->setResponse($event->getResponse()->getContent());
            }
        }

        $exception = $event->getException();
        $error = Error::fromException($exception);
        $this->request->addError($error);

        $this->request->setResponseCode(
            $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500
        );

        // lets notify only when error matches request log conditions
        if ($this->errorNotifier && $this->shouldLog($this->logConditions['request'])) {
            $url = $this->router->generate(
                'admin_daken_releaseprofiler_error_list',
                ['filter[reference][value]' => $error->getReference()],
                UrlGenerator::ABSOLUTE_URL
            );

            $this->errorNotifier->notify($error, $url);
        }
    }

    public function shouldLog($conditions)
    {
        foreach ($conditions as $condition) {
            $exclude = isset($condition['exclude']) && $condition['exclude'] ? true : false;

            $result = false;
            foreach ($condition as $part => $value) {
                switch ($part) {
                    case 'error':
                        $errors = $this->request->getErrors();
                        if (count($errors)) {
                            $error = $errors->first();
                            if ($value === true) {
                                $result = true;
                            } else {
                                $result = $error->getExceptionClass() == $value;
                            }
                        }
                        break;
                    case 'always':
                        $result = true;
                        break;
                    case 'host':
                        $result = $this->request->getHost() == $value;
                        break;
                    case 'path_preg':
                        $result = preg_match($value, $this->request->getPath());
                        break;
                    case 'route':
                        $result = $this->request->getMatchedRoute() == $value;
                        break;
                }
            }

            if ($exclude && $result) {
                return false;
            }

            if ($result) {
                return $result;
            }
        }
        return false;
    }

    public function onKernelTerminate()
    {
        if (!$this->shouldLog($this->logConditions['request'])) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                $this->request->setUsername($user->getUsername());
            } elseif (is_string($user)) {
                $this->request->setUsername($user);
            }
        }

        foreach ($this->sqlLogger->getQueries() as $query) {
            $this->request->addDatabaseQuery($query);
        }

        $this->request->setTotalDatabaseQueryCount($this->sqlLogger->getQueryCount());
        $this->request->setTotalDatabaseQueryTime($this->sqlLogger->getTotalTime());

        $this->request->stop();
        $this->persistManager->persist($this->request);
    }
}
