<?php

namespace Daken\ReleaseProfilerBundle\Tests\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;
use Daken\ReleaseProfilerBundle\PersistManager\DatabasePersistManager;

class DatabasePersistManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testPersistManager()
    {
        $request = new Request();
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->getMock();
        $em
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($request))
        ;

        $em
            ->expects($this->once())
            ->method('flush')
        ;

        $pm = new DatabasePersistManager($em);
        $pm->persist($request);
        $this->assertNull($pm->getPendingRequest());

        $this->assertEquals($pm->getEntityManager(), $em);
    }
}
