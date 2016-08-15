<?php

namespace Daken\ReleaseProfilerBundle\Tests\Entity;

use Daken\ReleaseProfilerBundle\Entity\Error;
use Daken\ReleaseProfilerBundle\Entity\Request;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $error = Error::fromException(new \Exception("test"));
        $this->assertEquals((string)$error, "test");
        $this->assertNull($error->getId());

        $request = new Request();
        $error->setRequest($request);
        $this->assertEquals($error->getRequest(), $request);

        $ref = '123';
        $error->setReference($ref);
        $this->assertEquals($error->getReference(), $ref);

        $dt = new \DateTime();
        $error->setCreated($dt);
        $this->assertEquals($dt, $error->getCreated());
    }
}
