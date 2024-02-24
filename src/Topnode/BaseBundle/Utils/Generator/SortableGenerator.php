<?php

namespace App\Topnode\BaseBundle\Utils\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class SortableGenerator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $kernel = $this->container->get('kernel');

        $resources = [
            $kernel->getProjectDir() . '/config/topnode/sortable.yml',
        ];

        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $aux = explode('\\', $bundle);
            $bundle = end($aux);

            $resources[] = $this->container
                ->get('kernel')
                ->locateResource('@' . $bundle)
                . 'Resources/config/topnode/sortable.yml'
            ;
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();

        $this->data = [];
        foreach ($resources as $resource) {
            if (!$fs->exists($resource)) {
                continue;
            }

            $data = Yaml::parseFile($resource);
            if (!is_array($data)) {
                continue;
            }

            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Returns the complete breadcrumb for the passed route.
     *
     * @param string $route
     */
    public function get($route): array
    {
        if (!array_key_exists($route, $this->data)) {
            return [];
        }

        $data = $this->data[$route];

        if (array_key_exists('inherit', $data) && strlen($data['inherit']) > 0) {
            $data = array_merge($this->get($data['inherit']), $data);
            unset($data['inherit']);
        }

        return $data;
    }
}
