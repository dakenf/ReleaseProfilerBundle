<?php

namespace Daken\ReleaseProfilerBundle\Tests\Entity;


use Daken\ReleaseProfilerBundle\Entity\DatabaseQuery;
use Daken\ReleaseProfilerBundle\Entity\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request();

        $dt = new \DateTime();
        $request->setCreated($dt);
        $this->assertEquals($dt, $request->getCreated());

        $request->stop();
        $this->assertGreaterThan(0, $request->getTime());

        $this->assertNull($request->getId());


        $requestContent = 'test request';
        $stream = fopen('data://text/plain,' . $requestContent, 'r');
        $request->setResponse($stream);

        $this->assertEquals($requestContent, $request->responseAsString());

        $this->assertEquals(0, count($request->getDatabaseQueries()));

        $q = new DatabaseQuery();
        $request->addDatabaseQuery($q);
        $this->assertEquals(1, count($request->getDatabaseQueries()));
        $request->removeDatabaseQuery($q);
        $this->assertEquals(0, count($request->getDatabaseQueries()));
    }
}
