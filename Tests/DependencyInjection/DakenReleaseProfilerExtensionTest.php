<?php

namespace Daken\ReleaseProfilerBundle\Tests\DependencyInjection;

use Daken\ReleaseProfilerBundle\DependencyInjection\DakenReleaseProfilerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

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


    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "persist_manager" at path "daken_release_profiler" must be configured.
     */
    public function testEmptyConfig()
    {
        $this->load();
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration condition 'request' must be an array.
     */
    public function testWrongConditions()
    {
        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [''],
                'request_body' => [''],
                'response_body' => [''],
            ]
        ]);
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration condition 'request' is empty.
     */
    public function testWrongConditions2()
    {
        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [[]],
                'request_body' => [''],
                'response_body' => [''],
            ]
        ]);
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Unknown configuration condition 'test' under 'request'.
     */
    public function testWrongConditions3()
    {
        $this->load([
            'persist_manager' => 'redis',
            'log_conditions' => [
                'request' => [['test' => false]],
                'request_body' => [['test']],
                'response_body' => [['test']],
            ]
        ]);
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid persist manager name: invalid. It should be 'database', 'redis' or a
     *                              service name.
     */
    public function testInvalidPersistManager()
    {
        $config = $this->getValidConfig();
        $config['persist_manager'] = 'invalid';
        $this->load($config);
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage You must set a service for redis.
     */
    public function testEmptyRedisConfig()
    {
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

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid error notifier name: invalid. It should be 'slack', 'null' or a service name.
     */
    public function testInvalidErrorNotifier()
    {
        $config = $this->getValidConfig();
        $config['error_notifier'] = 'invalid';
        $this->load($config);
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage You should define a hook url for slack.
     */
    public function testSlackInvalidConfig()
    {
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

    public function testNullNotifier()
    {
        $config = $this->getValidConfig();
        $config['error_notifier'] = null;

        $this->load($config);

        $this->assertContainerBuilderHasAlias(
            'daken_release_profiler.error_notifier',
            'daken_release_profiler.error_notifier.null'
        );
    }

    /**
     * @expectedException        Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage You must set entity_manager parameter.
     */
    public function testDatabaseEntityManager()
    {
        $config = $this->getValidConfig();
        unset($config['entity_manager']);

        $this->load($config);
    }
}
