<?php

namespace Daken\ReleaseProfilerBundle\Tests\DependencyInjection;

use Daken\ReleaseProfilerBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testValuesAreInvalidIfRequiredValueIsNotProvided()
    {
        $this->assertConfigurationIsInvalid(
            [
                []
            ]
        );

        $this->assertConfigurationIsInvalid(
            [
                ['persist_manager' => 'redis']
            ]
        );

        $this->assertConfigurationIsValid(
            [
                [
                    'persist_manager' => 'redis',
                    'log_conditions' => [
                        'request' => [],
                        'request_body' => [],
                        'response_body' => [],
                    ]
                ]
            ]
        );
    }
}
