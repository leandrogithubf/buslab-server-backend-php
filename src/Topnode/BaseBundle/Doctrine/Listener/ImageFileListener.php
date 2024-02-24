<?php

namespace App\Topnode\BaseBundle\Doctrine\Listener;

use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use App\Topnode\BaseBundle\Utils\String\Identifiern;
use Doctrine\ORM\Event\LifeCycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;

class ImageFileListener
{
    private $fileHandler;
    private $filesystem;

    public function __construct(FileHandler $fileHandler, Filesystem $filesystem)
    {
        $this->fileHandler = $fileHandler;
        $this->filesystem = $filesystem;
    }

    /**
     * Called on Topnode\AppBundle\DependencyInjection\TopnodeAppExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function prePersist(LifeCycleEventArgs $event)
    {
        $object = $event->getObject();

        if (property_exists($object, 'tempImageFile')
            && null !== $object->getTempImageFile()
        ) {
            if (property_exists($object, 'imageIdentifier')
                && null === $object->getImageIdentifier()
            ) {
                $em = $event->getEntityManager();
                $entityClassName = $em->getClassMetadata(get_class($object))->getName();
                do {
                    $identifier = Identifiern::filename();
                } while (is_object($em->getRepository($entityClassName)->findOneByImageIdentifier($identifier)));

                $object->setImageIdentifier($identifier);
            }

            if (null === $object->getImagePath()) {
                $this->setImagePahtAndUpload($object, $event);
            } else {
                $this->updateImageFile($object);
            }
        }
    }

    private function updateImageFile($object)
    {
        $this->filesystem->remove([$object->getImagePath()]);
        $uploaded = $this->fileHandler->upload(
            $object->getTempImageFile(),
            $object->getImageIdentifier()
        );

        $object->setImagePath($uploaded->getPathName());
    }

    private function setImagePahtAndUpload($object, $event)
    {
        $uploaded = $this->fileHandler->upload(
            $object->getTempImageFile(),
            $object->getImageIdentifier()
        );

        $object->setTempImageFile(null);
        $object->setImagePath($uploaded->getPathName());
    }
}
