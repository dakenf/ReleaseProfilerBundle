<?php

namespace Daken\ReleaseProfilerBundle\Tests\Notifier;

use Daken\ReleaseProfilerBundle\Entity\Error;
use Daken\ReleaseProfilerBundle\Notifier\SlackNotifier;

class SlackNotifierTest extends \PHPUnit_Framework_TestCase
{
    public function testNotify()
    {
        $postResult = null;
        $targetUrl = null;

        $guzzle = $this->createMock('Daken\ReleaseProfilerBundle\Tests\Notifier\TestGuzzleInterface');
        $guzzle
            ->expects($this->once())
            ->method('post')
            ->with($this->anything())
            ->willReturnCallback(function ($url, $options) use (&$postResult, &$targetUrl) {
                $postResult = $options['body'];
                $targetUrl = $url;
            });

        $slackUrl = 'http://aaa.com';
        $notifier = new SlackNotifier($guzzle, $slackUrl, 'username', 'icon');

        $error = Error::fromException(new \Exception());

        $notifier->notify($error, $slackUrl);

        $this->assertGreaterThan(0, strpos($postResult, (string)$error->getReference()));
        $this->assertEquals($targetUrl, $slackUrl);
    }
}
