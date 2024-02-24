<?php

namespace App\Topnode\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="topnode_file_")
 */
class FileController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'service_container' => \Symfony\Component\DependencyInjection\ContainerInterface::class,
        ]);
    }

    /**
     * Downloads a file from the given identifier.
     *
     * @Route(
     *     "/download/{identifier}",
     *     name="download",
     *     methods="GET",
     *     requirements={"identifier"="^[0-9A-Za-z\-\_]{15}$"}
     * )
     *
     * @param string $id The file identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downladAction(string $identifier)
    {
        $entity = $this->get('service_container')
            ->get('tn.file.repository')
            ->findOneByIdentifier($identifier)
        ;

        if (null === $entity) {
            return $this->get('service_container')
                ->get('tn.utils.api.response')
                ->error(404, 'file not found')
            ;
        }

        return $this->getFileHandler()
            ->download($entity->getPath(), $entity->getOriginalName())
        ;
    }

    /**
     * View a file from the given id.
     *
     * @Route(
     *     "/view/{id}",
     *     name="view",
     *     methods="GET",
     * )
     *
     * @param string $id The file identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(string $identifier = '0')
    {
        $entity = $this->get('service_container')
            ->get('tn.file.repository')
            ->findOneByIdentifier($identifier)
        ;

        if (null === $entity) {
            return $this->get('service_container')
                ->get('tn.utils.api.response')
                ->error(404, 'file not found')
            ;
        }

        return $this->getFileHandler()
            ->view($entity->getPath(), $entity->getOriginalName())
        ;
    }

    protected function getFileHandler()
    {
        return $this->get('service_container')->get('tn.file.handler.decisor')->getHandler();
    }
}
