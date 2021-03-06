<?php

namespace Daken\ReleaseProfilerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DakenReleaseProfilerBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        $doctrineConfig = $this->container->get('doctrine')
            ->getConnection()
            ->getConfiguration();

        if (php_sapi_name() != 'cli' || $this->container->hasParameter('daken_release_profiler.force_sql_logger')) {
            $logger = $this->container->get('daken_release_profiler.sql_logger');
            $logger->setOldLogger($doctrineConfig->getSQLLogger());
            $doctrineConfig->setSQLLogger($logger);
        }
    }
}
