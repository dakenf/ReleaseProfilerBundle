<?php

namespace Daken\ReleaseProfilerBundle\Notifier;

use Daken\ReleaseProfilerBundle\Entity\Error;

interface NotifierInterface
{
    public function notify(Error $error, $url);
}
