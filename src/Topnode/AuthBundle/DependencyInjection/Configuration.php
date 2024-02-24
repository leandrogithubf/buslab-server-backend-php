<?php

namespace App\Topnode\AuthBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('topnode_auth');

        // Recaptcha
        $rootNode
            ->children()
                ->arrayNode('recaptcha')
                    ->children()
                        ->scalarNode('url')->defaultValue('https://www.google.com/recaptcha/api/siteverify')->cannotBeEmpty()->end()
                        ->scalarNode('version')->defaultValue('v2')->cannotBeEmpty()->end()
                        ->scalarNode('sitekey')->cannotBeEmpty()->end()
                        ->scalarNode('secretkey')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
