<?php

namespace App\Topnode\BaseBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifeCycleEventArgs;

class ProtocolListener
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

        if (property_exists($object, 'protocol')) {
            $em = $event->getEntityManager();
            $entityClassName = $em->getClassMetadata(get_class($object))->getName();

            $now = new \DateTime();
            $start = (clone $now)->setTime(0, 0, 0);
            $end = (clone $now)->setTime(23, 59, 59);

            $qtt = $em->getRepository($entityClassName)
                ->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->andWhere('e.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult()
            ;

            $qtt = $qtt + 1;

            $protocol = $now->format('Ymd') . '-' . str_repeat('0', 5 - strlen($qtt)) . $qtt;

            $object->setProtocol($protocol);
        }
    }
}
