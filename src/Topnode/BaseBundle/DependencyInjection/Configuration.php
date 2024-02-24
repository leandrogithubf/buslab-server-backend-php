<?php

namespace App\Topnode\BaseBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('topnode_base');

        // System and doctrine configuration for entities automation
        $rootNode
            ->children()
                ->booleanNode('auto_filter_inactive')
                    ->defaultValue(true)
                    ->info('Whether or not the inactive sql filter should be used or not (rows in the database with is_active = 0 won\'t be loaded)')
                ->end()
                ->booleanNode('auto_generate_identifier')
                    ->defaultValue(true)
                    ->info('Whether or not entities with the field identifier should be populated with a random and unique by table value')
                ->end()
                ->booleanNode('auto_generate_slug')
                    ->defaultValue(true)
                    ->info('Whether or not entities with the field slug should be populated with a unique (by table) value')
                ->end()
                ->booleanNode('auto_generate_timestamps')
                    ->defaultValue(true)
                    ->info('Whether or not to auto generate date-time stamps on create, uppdate and soft remove.')
                ->end()
            ->end()
        ;

        // System view information regarding the company
        $rootNode
            ->children()
                ->arrayNode('environment_defaults')
                    ->info('Configures information regarding the environment.')
                    ->isRequired()
                    ->children()
                        ->scalarNode('since')->isRequired()->cannotBeEmpty()->info('System\'s start year')->end()
                        ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('slogan')->end()
                        ->scalarNode('description')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('logo')
                            ->info('Logo public path images for the system')
                            ->children()
                                ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('login')->end()
                                ->scalarNode('menu')->end()
                                ->scalarNode('xs')->end()
                                ->scalarNode('sm')->end()
                                ->scalarNode('md')->end()
                                ->scalarNode('lg')->end()
                            ->end()
                        ->end()
                        ->arrayNode('front')->info('The frontend files information (favicon, browserconfig and manifest.json)')
                            ->children()
                                ->booleanNode('manage_front_files')->defaultValue(true)->info('Whether or not the fron should return 404 or generate the files via PHP')->isRequired()->end()
                                ->arrayNode('browserconfig')
                                    ->info('The frontend information')
                                    ->children()
                                        ->scalarNode('tilecolor')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('manifest')
                                    ->info('The frontend information')
                                    ->children()
                                        ->scalarNode('offline_enabled')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('theme_color')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('background_color')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('environment')
                    ->info('Configures information regarding the current by domain environment.')
                    ->useAttributeAsKey('domain')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('since')->cannotBeEmpty()->info('System\'s start year')->end()
                            ->scalarNode('name')->end()
                            ->scalarNode('slogan')->end()
                            ->scalarNode('description')->end()
                            ->arrayNode('logo')
                                ->info('Logo public path images for the system')
                                ->children()
                                    ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('login')->end()
                                    ->scalarNode('xs')->end()
                                    ->scalarNode('sm')->end()
                                    ->scalarNode('md')->end()
                                    ->scalarNode('lg')->end()
                                ->end()
                            ->end()
                            ->arrayNode('front')->info('The frontend files information (favicon, browserconfig and manifest.json)')
                            ->children()
                                ->booleanNode('manage_front_files')->defaultValue(true)->info('Whether or not the fron should return 404 or generate the files via PHP')->isRequired()->end()
                                ->arrayNode('browserconfig')
                                    ->info('The frontend information')
                                    ->children()
                                        ->scalarNode('tilecolor')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('manifest')
                                    ->info('The frontend information')
                                    ->children()
                                        ->scalarNode('offline_enabled')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('theme_color')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('background_color')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        // System icon family
        $rootNode
            ->children()
                ->scalarNode('icon_family')
                    ->cannotBeEmpty()
                    ->defaultValue('fa')
                    ->info('This value is used to add icons on the view (fa, zmdi, false). If no icon needed, use false.')
                ->end()
                ->arrayNode('icons')
                    ->children()
                        ->scalarNode('desc')->defaultValue('fa-sort-down')->end()
                        ->scalarNode('asc')->defaultValue('fa-sort-up')->end()
                        ->scalarNode('sort')->defaultValue('fa-sort')->end()
                        ->scalarNode('index')->defaultValue('fa-arrow-left')->end()
                        ->scalarNode('new')->defaultValue('fa-plus-circle')->end()
                        ->scalarNode('upload')->defaultValue('fa-upload')->end()
                        ->scalarNode('edit')->defaultValue('fa-edit')->end()
                        ->scalarNode('password')->defaultValue('fa-key')->end()
                        ->scalarNode('delete')->defaultValue('fa-minus-circle')->end()
                        ->scalarNode('show')->defaultValue('fa-info-circle')->end()
                        ->scalarNode('setting')->defaultValue('fa-cogs')->end()
                        ->scalarNode('left')->defaultValue('fa-chevron-left')->end()
                        ->scalarNode('right')->defaultValue('fa-chevron-right')->end()
                        ->scalarNode('duplicate')->defaultValue('fa-clone')->end()
                        ->scalarNode('info')->defaultValue('fa-info')->end()
                        ->scalarNode('options')->defaultValue('fa-ellipsis-v')->end()
                        ->scalarNode('close')->defaultValue('fa-times')->end()
                        ->scalarNode('loader')->defaultValue('fa-spinner')->end()
                        ->scalarNode('success')->defaultValue('fa-success-circle')->end()
                        ->scalarNode('check')->defaultValue('fa-check')->end()
                        ->scalarNode('error')->defaultValue('fa-times-circle')->end()
                        ->scalarNode('warning')->defaultValue('fa-exclamation-circle')->end()
                        ->scalarNode('backward')->defaultValue('fa-arrow-left')->end()
                        ->scalarNode('forward')->defaultValue('fa-arrow-right')->end()
                        ->scalarNode('export')->defaultValue('fa-download')->end()
                    ->end()
                ->end()
            ->end()
        ;

        // Mailer
        $rootNode
            ->children()
                ->arrayNode('mailer')
                    ->children()
                        ->scalarNode('default_from_name')->defaultValue('top(node)')->cannotBeEmpty()->end()
                        ->scalarNode('default_from_email')->defaultValue('contato@topnode.com.br')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
