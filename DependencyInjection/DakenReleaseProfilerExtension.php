<?php

namespace Daken\ReleaseProfilerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DakenReleaseProfilerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);


        $container->setParameter(
            'daken_release_profiler.database.query_time_log_threshold',
            $config['database_query_time_log_threshold']
        );

        $conditionsAvailable = ['error', 'always', 'host', 'path_preg', 'route', 'exclude'];
        foreach ($config['log_conditions'] as $class => $classConditions) {
            foreach ($classConditions as $conditions) {
                if (!is_array($conditions)) {
                    throw new InvalidConfigurationException(
                        "Configuration condition '{$class}' must be an array."
                    );
                }

                if (count($conditions) == 0) {
                    throw new InvalidConfigurationException(
                        "Configuration condition '{$class}' is empty."
                    );
                }

                foreach ($conditions as $condition => $value) {
                    if (in_array($condition, $conditionsAvailable, true) === false) {
                        throw new InvalidConfigurationException(
                            "Unknown configuration condition '{$condition}' under '{$class}'."
                        );
                    }
                }
            }
        }

        $container->setParameter('daken_release_profiler.log_conditions', $config['log_conditions']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $notifierName = $config['error_notifier'];
        if ($notifierName == 'slack') {
            if (!isset($config['slack']['hook_url'])) {
                throw new InvalidConfigurationException("You should define a hook url for slack.");
            }

            $container->setParameter('daken_release_profiler.slack.hook_url', $config['slack']['hook_url']);

            $container->setParameter('daken_release_profiler.slack.emoji', $config['slack']['emoji']);
            $container->setParameter('daken_release_profiler.slack.username', $config['slack']['username']);
        }

        $loader->load('services.yml');

        if ($notifierName == null) {
            $notifierName = 'null';
        }

        $container->setAlias(
            'daken_release_profiler.guzzle_client',
            'daken_release_profiler.guzzle_client.default'
        );

        if (!$container->has($notifierName)) {
            if ($notifierName == 'slack') {
                $loader->load('slack.yml');
            }

            $notifierPrefix = 'daken_release_profiler.error_notifier.';
            if (!$container->has($notifierPrefix . $notifierName)) {
                throw new InvalidConfigurationException(
                    "Invalid error notifier name: {$notifierName}. It should be 'slack', 'null' or a service name."
                );
            }
            $notifierName = $notifierPrefix.$notifierName;
        }

        $container->addAliases(['daken_release_profiler.error_notifier' => $notifierName]);

        if ($config['enable_sonata']) {
            $loader->load('admin.yml');
        }
        
        $persistManagerName = $config['persist_manager'];
        if ($persistManagerName == 'redis') {
            if (!isset($config['redis']['service'])) {
                throw new InvalidConfigurationException("You must set a service for redis.");
            }

            $container->addAliases(['daken_release_profiler.redis.backend' => $config['redis']['service']]);
            $container->setParameter('daken_release_profiler.redis.key_prefix', $config['redis']['key_prefix']);
            $loader->load('redis.yml');
        } elseif ($persistManagerName == 'database') {
            if (!isset($config['entity_manager'])) {
                throw new InvalidConfigurationException("You must set entity_manager parameter.");
            }
        }

        $entityManager = isset($config['entity_manager']) ?
            $config['entity_manager'] : 'doctrine.orm.default_entity_manager';
        $container->addAliases(
            ['daken_release_profiler.database.entity_manager' => $entityManager]
        );

        if (!$container->has($persistManagerName)) {
            $managerPrefix = 'daken_release_profiler.persist_manager.';
            if (!$container->has($managerPrefix . $persistManagerName)) {
                throw new InvalidConfigurationException(
                    "Invalid persist manager name: {$persistManagerName}. ".
                    "It should be 'database', 'redis' or a service name."
                );
            }
            $persistManagerName = $managerPrefix.$persistManagerName;
        }

        $container->addAliases(['daken_release_profiler.persist_manager' => $persistManagerName]);
    }
}
