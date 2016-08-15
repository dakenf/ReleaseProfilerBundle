<?php

namespace Daken\ReleaseProfilerBundle\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;

interface PersistManagerInterface
{
    public function persist(Request $request);

    /**
     * @return Request|boolean
     */
    public function getPendingRequest($blockTime = null);
}
