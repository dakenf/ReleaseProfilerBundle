<?php

namespace Daken\ReleaseProfilerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('daken_release_profiler');

        $rootNode
            ->children()
                ->scalarNode('persist_manager')
                    ->isRequired()
                ->end()
                ->arrayNode('redis')
                    ->children()
                        ->scalarNode('service')->end()
                        ->scalarNode('key_prefix')->defaultValue('daken_release_profiler')->end()
                    ->end()
                ->end()
                ->arrayNode('error_log')
                    ->children()
                        ->scalarNode('service')->end()
                        ->arrayNode('conditions')->end()
                    ->end()
                ->end()
                ->arrayNode('log_conditions')
                    ->isRequired()
                    ->children()
                        ->variableNode('request')->isRequired()->end()
                        ->variableNode('request_body')->isRequired()->end()
                        ->variableNode('response_body')->isRequired()->end()
                    ->end()
                ->end()
                ->booleanNode('enable_sonata')->defaultFalse()->end()
                ->booleanNode('enable_stopwatch')->defaultFalse()->end()
                ->integerNode('database_query_time_log_threshold')->defaultValue(0)->end()
                ->scalarNode('error_notifier')->defaultValue('null')->end()

                ->arrayNode('slack')
                    ->children()
                        ->scalarNode('hook_url')->end()
                        ->scalarNode('emoji')->defaultNull()->end()
                        ->scalarNode('username')->defaultNull()->end()
                    ->end()
                ->end()

                ->scalarNode('entity_manager')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
