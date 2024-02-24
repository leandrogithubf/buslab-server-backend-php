<?php

namespace App\Topnode\BaseBundle\Doctrine\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class DoctrineReactivateEntityListener
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Called on App\Topnode\BaseBundle\DependencyInjection\TopnodeBaseExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->config['auto_filter_inactive']) {
            return;
        }

        $request = $event->getRequest();
        if (false !== strpos($request->get('_route', null), '_reactivate')) {
            $this->em->getFilters()->disable('tn.doctrine.listener.deactivate');
        }
    }
}
