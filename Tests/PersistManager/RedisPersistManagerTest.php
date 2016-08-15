<?php

namespace Daken\ReleaseProfilerBundle\Tests\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;
use Daken\ReleaseProfilerBundle\PersistManager\RedisPersistManager;

class RedisPersistManagerTest extends \PHPUnit_Framework_TestCase
{
    public function createPersistManager()
    {
        $dummyRedis = new TestRedis();
        return new RedisPersistManager($dummyRedis, 'daken_release_profiler');
    }

    public function testPersist()
    {
        $rp = $this->createPersistManager();
        $request = new Request();
        $rp->persist($request);

        $this->assertEquals($rp->getPendingRequest(), $request);

        $this->assertNull($rp->getPendingRequest());

        $rp->persist($request);
        $this->assertEquals($rp->getPendingRequest(10), $request);
        $this->assertNull($rp->getPendingRequest(10));
    }
}
