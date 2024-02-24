<?php

namespace App\Topnode\BaseBundle\Doctrine\Listener;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Doctrine\ORM\Event\LifeCycleEventArgs;

class UniqueSlugListener
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
        if (!$this->config['auto_generate_slug']) {
            return;
        }

        $object = $event->getEntity();

        if (property_exists($object, 'slug')) {
            $em = $event->getEntityManager();
            $metadata = $em->getClassMetadata(get_class($object));
            $entityClassName = $metadata->getName();

            $slug = $object->getSlug();
            if (0 === strlen($slug)) {
                if (property_exists($object, 'title') && strlen($object->getTitle()) > 0) {
                    $slug = $object->getTitle();
                } elseif (property_exists($object, 'description') && strlen($object->getDescription()) > 0) {
                    $slug = $object->getDescription();
                } elseif (property_exists($object, 'identifier') && strlen($object->getIdentifier()) > 0) {
                    $slug = $object->getIdentifier();
                } else {
                    $slug = Identifier::filename(30);
                }
            }

            // Max length is 4 chars shorter than the field size to allow up to "999" duplicitys appended at the end
            // with a separator dash (e.g. -123)
            $maxlength = $metadata->fieldMappings['slug']['length'] - 4;

            $slug = substr(StringUtils::slugify($slug), 0, $maxlength);

            // To handl duplicity we keep the qtt of the slug and the original value
            $counter = 1;
            $slugDuplicity = $slug;

            while (is_object($em->getRepository($entityClassName)->findOneBySlug($slugDuplicity))) {
                $slugDuplicity = $slug . '-' . (++$counter);
            }

            $object->setSlug($slugDuplicity);
        }
    }
}
