<?php

namespace Daken\ReleaseProfilerBundle\Tests;

use Daken\ReleaseProfilerBundle\DakenReleaseProfilerBundle;
use Daken\ReleaseProfilerBundle\Logging\SQLLogger;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DakenReleaseProfilerBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBoot()
    {
        $testLogger = new SQLLogger();

        $configuration = $this->getMockBuilder('Doctrine\DBAL\Configuration')->getMock();
        $configuration
            ->expects($this->once())
            ->method('getSQLLogger')
            ->willReturn(null)
        ;

        $configuration
            ->expects($this->once())
            ->method('setSQLLogger')
            ->with($testLogger)
        ;

        $driver = new Driver();

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->setConstructorArgs([[], $driver])
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\AbstractManagerRegistry')
            ->setConstructorArgs(['asd', [], [], null, null, null])
            ->getMock();
        $doctrine
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $container = new ContainerBuilder();
        $container->set('doctrine', $doctrine);
        $container->setParameter('daken_release_profiler.force_sql_logger', 1);
        $container->set('daken_release_profiler.sql_logger', $testLogger);

        $bundle = new DakenReleaseProfilerBundle();
        $bundle->setContainer($container);
        $bundle->boot();
    }
}
