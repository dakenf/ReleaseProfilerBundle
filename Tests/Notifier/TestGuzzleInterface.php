<?php
namespace Daken\ReleaseProfilerBundle\Tests\Notifier;

interface TestGuzzleInterface
{
    public function post($url, $options);
}
