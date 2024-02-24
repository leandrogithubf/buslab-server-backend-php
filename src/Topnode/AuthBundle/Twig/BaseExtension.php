<?php

namespace App\Topnode\AuthBundle\Twig;

class BaseExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * Called on Topnode\BaseBundle\DependencyInjection\TopnodeBaseExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getFilters()
    {
        return [];
    }

    public function getGlobals()
    {
        return [
            'recaptcha_sitekey' => $this->config['recaptcha']['sitekey'],
        ];
    }

    public function getName()
    {
        return 'tn_auth_extension';
    }
}
