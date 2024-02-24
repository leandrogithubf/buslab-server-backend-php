<?php

namespace App\Topnode\BaseBundle\Utils\Configurator;

use Symfony\Component\HttpFoundation\RequestStack;

class Environment
{
    /**
     * The environment configuration, such as name, domain and logo.
     *
     * @var array
     */
    private $config;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function get($info = null)
    {
        if (strlen($info) > 0) {
            return $this->config[$info];
        }

        return $this->config;
    }

    /**
     * Called on Topnode\BaseBundle\DependencyInjection\TopnodeAppExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config): void
    {
        $this->config = $this->merge(
            $config['environment_defaults'],
            $this->decide($config['environment'])
        );
    }

    private function decide(array $customs): array
    {
        if ($this->request && array_key_exists($this->request->getHost(), $customs)) {
            return $customs[$this->request->getHost()];
        }

        return [];
    }

    private function merge(array $defaults, array $custom): array
    {
        if (0 === count($custom)) {
            return $defaults;
        }

        foreach ($custom as $key => $data) {
            if (is_array($data)) {
                foreach ($data as $key2 => $data2) {
                    $defaults[$key][$key2] = $data2;
                }
            } else {
                $defaults[$key] = $data;
            }
        }

        return $defaults;
    }
}
