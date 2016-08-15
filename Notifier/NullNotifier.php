<?php

namespace Daken\ReleaseProfilerBundle\Notifier;

use Daken\ReleaseProfilerBundle\Entity\Error;

class NullNotifier implements NotifierInterface
{
    public function notify(Error $error, $url)
    {
    }
}
