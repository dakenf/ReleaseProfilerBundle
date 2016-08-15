<?php

namespace Daken\ReleaseProfilerBundle\Tests\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;
use Daken\ReleaseProfilerBundle\PersistManager\PersistManagerInterface;

class TestPersistManager implements PersistManagerInterface
{
    private $requests = array();

    public function persist(Request $request)
    {
        $this->requests[] = $request;
    }

    public function getPendingRequest($blockTime = null)
    {
        if (count($this->requests)) {
            return array_shift($this->requests);
        }

        return null;
    }
}
