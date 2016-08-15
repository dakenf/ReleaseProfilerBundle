<?php

namespace Daken\ReleaseProfilerBundle\Tests\PersistManager;

class TestRedis extends \Redis
{
    private $data = [];

    public function rPush($key, $value1, $value2 = null, $valueN = null)
    {
        $this->data[] = $value1;
    }

    public function lPop($key)
    {
        if (count($this->data) == 0) {
            return null;
        }

        return array_shift($this->data);
    }

    public function blPop($key, $blockTime)
    {
        usleep(100000);
        $result = $this->lPop($key);
        if (!$result) {
            return false;
        }

        return [0, $result];
    }
}
