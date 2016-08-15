<?php

namespace Daken\ReleaseProfilerBundle\Twig;

class ProfilerExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('profiler_format_sql', array($this, 'formatSql')),
        );
    }

    public function formatSql($sql)
    {
        $formatter = new \SqlFormatter();
        return $formatter->format($sql);
    }

    public function getName()
    {
        return 'daken_release_profiler_extension';
    }
}
