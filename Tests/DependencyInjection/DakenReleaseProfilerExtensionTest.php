<?php

namespace Daken\ReleaseProfilerBundle\Tests\DependencyInjection;

use Daken\ReleaseProfilerBundle\DependencyInjection\DakenReleaseProfilerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class DakenReleaseProfilerExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new DakenReleaseProfilerExtension()
        );
    }

    private function getValidConfig()
    {
        return [
            'persist_manager' => 'database',
            'entity_manager' => 'doctrine.orm.default_entity_manager',
            'log_conditions' => [
                'request' => [['always' => true]],
                'request_body' => [['always' => true]],
                'response_body' => [['always' => true]],
            ]
        ];
    }


    public function testEmptyConfig()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "The child node \"persist_manager\" at path \"daken_release_profiler\" must be configured."
        );
        $this->load();
    }

    public function testWrongConditions()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "Configuration condition 'request' must be an array."
        );

        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [''],
                'request_body' => [''],
                'response_body' => [''],
            ]
        ]);
    }

    public function testWrongConditions2()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "Configuration condition 'request' is empty."
        );

        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [[]],
                'request_body' => [''],
                'response_body' => [''],
            ]
        ]);
    }

    public function testWrongConditions3()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "Unknown configuration condition 'test' under 'request'."
        );

        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [['test' => false]],
                'request_body' => [['test']],
                'response_body' => [['test']],
            ]
        ]);
    }

    public function testInvalidPersistManager()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "Invalid persist manager name: invalid. It should be 'database', 'redis' or a service name."
        );

        $config = $this->getValidConfig();
        $config['persist_manager'] = 'invalid';
        $this->load($config);
    }

    public function testEmptyRedisConfig()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "You must set a service for redis."
        );

        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [['always' => true]],
                'request_body' => [['always' => true]],
                'response_body' => [['always' => true]],
            ]
        ]);

        $this->assertContainerBuilderHasParameter('daken_release_profiler.redis.key_prefix', 'daken_release_profiler');
        $this->assertContainerBuilderHasAlias('daken_release_profiler.persist_manager');
    }

    public function testValidRedisConfig()
    {
        $this->container->set('redis_service', $this);

        $config = $this->getValidConfig();
        $config['persist_manager'] = 'redis';
        $config['redis'] = ['service' => 'redis_service'];

        $this->load($config);

        $this->assertContainerBuilderHasParameter('daken_release_profiler.redis.key_prefix', 'daken_release_profiler');
    }

    public function testSonata()
    {
        $config = $this->getValidConfig();
        $config['enable_sonata'] = true;
        $this->load($config);

        $this->assertContainerBuilderHasService('daken_release_profiler.admin.request');
        $this->assertContainerBuilderHasService('daken_release_profiler.admin.error');
        $this->assertContainerBuilderHasService('daken_release_profiler.admin.database_query');
    }

    public function testInvalidErrorNotifier()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "Invalid error notifier name: invalid. It should be 'slack', 'null' or a service name."
        );

        $config = $this->getValidConfig();
        $config['error_notifier'] = 'invalid';
        $this->load($config);
    }

    public function testSlackInvalidConfig()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "You should define a hook url for slack."
        );

        $config = $this->getValidConfig();
        $config['error_notifier'] = 'slack';

        $this->load($config);
    }

    public function testSlackNotifier()
    {
        $config = $this->getValidConfig();
        $config['error_notifier'] = 'slack';
        $config['slack']['hook_url'] = 'http://slack.com';
        $config['slack']['username'] = 'username';
        $config['slack']['emoji'] = 'emoji';
        $this->load($config);

        $this->assertContainerBuilderHasParameter('daken_release_profiler.slack.hook_url', 'http://slack.com');
        $this->assertContainerBuilderHasParameter('daken_release_profiler.slack.username', 'username');
        $this->assertContainerBuilderHasParameter('daken_release_profiler.slack.emoji', 'emoji');
    }

    public function testDatabaseEntityManager()
    {
        $this->expectException(get_class(new InvalidConfigurationException()));
        $this->expectExceptionMessage(
            "You must set entity_manager parameter."
        );

        $config = $this->getValidConfig();
        unset($config['entity_manager']);

        $this->load($config);
    }
}
