<?php

namespace Daken\ReleaseProfilerBundle\PersistManager;

use Daken\ReleaseProfilerBundle\Entity\Request;

class RedisPersistManager implements PersistManagerInterface
{
    private $redis;

    private $keyPrefix;

    public function __construct($redis, $keyPrefix)
    {
        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
    }

    public function serializeRequest(Request $request)
    {
        return serialize($request);
    }

    public function unserializeRequest($request)
    {
        if (!$request) {
            return $request;
        }

        return unserialize($request);
    }

    public function persist(Request $request)
    {
        $serializedRequest = $this->serializeRequest($request);

        $this->redis->rPush($this->keyPrefix, $serializedRequest);
    }

    public function getPendingRequest($blockTime = null)
    {
        if ($blockTime) {
            $result = $this->redis->blPop($this->keyPrefix, $blockTime);
            if ($result) {
                return $this->unserializeRequest($result[1]);
            }
            return null;
        }

        return $this->unserializeRequest($this->redis->lPop($this->keyPrefix));
    }
}
