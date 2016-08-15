<?php

namespace Daken\ReleaseProfilerBundle\Tests\Twig;

use Daken\ReleaseProfilerBundle\Twig\ProfilerExtension;

class ProfilerExtensionTest extends \PHPUnit_Framework_TestCase
{
    private function getProfilerExtension()
    {
        return new ProfilerExtension();
    }

    public function testGetFunctions()
    {
        $pf = $this->getProfilerExtension();
        $pf->getFunctions();

        $pf->formatSql("SELECT FOUND_ROWS()");
        $this->assertNotNull($pf->getName());
    }
}
