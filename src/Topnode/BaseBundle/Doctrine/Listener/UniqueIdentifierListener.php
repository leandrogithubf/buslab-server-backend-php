<?php

namespace App\Topnode\BaseBundle\Doctrine\Listener;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Doctrine\ORM\Event\LifeCycleEventArgs;

class UniqueIdentifierListener
{
    /**
     * Called on App\Topnode\BaseBundle\DependencyInjection\TopnodeBaseExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function prePersist(LifeCycleEventArgs $event)
    {
        if (!$this->config['auto_generate_identifier']) {
            return;
        }

        $object = $event->getEntity();

        if (property_exists($object, 'identifier')) {
            $em = $event->getEntityManager();
            $entityClassName = $em->getClassMetadata(get_class($object))->getName();

            do {
                $identifier = Identifier::database();
            } while (is_object($em->getRepository($entityClassName)->findOneByIdentifier($identifier)));

            $object->setIdentifier($identifier);
        }
    }
}
