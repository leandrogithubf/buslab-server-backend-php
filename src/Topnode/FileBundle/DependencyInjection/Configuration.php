<?php

namespace App\Topnode\FileBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('topnode_file');

        // Bundle configuration for selecting the file source
        $rootNode
            ->children()
                ->scalarNode('file_handler')
                    ->defaultValue('local_storage')
                    ->info('the handler of upload donwload and content')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
