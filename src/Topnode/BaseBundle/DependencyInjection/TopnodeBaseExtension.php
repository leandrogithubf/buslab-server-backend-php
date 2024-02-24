<?php

namespace App\Topnode\BaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TopnodeBaseExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $services = [
            'tn.base.twig.extension',
            'tn.configurator.environment',
            'tn.doctrine.listener.reactivate',
            'tn.doctrine.listener.identifier',
            'tn.doctrine.listener.protocol',
            'tn.doctrine.listener.slug',
            'tn.doctrine.listener.timestamp',
            'tn.mailer',
        ];

        foreach ($services as $service) {
            $container
                ->getDefinition($service)
                ->addMethodCall('setConfig', [$config])
            ;
        }
    }
}
