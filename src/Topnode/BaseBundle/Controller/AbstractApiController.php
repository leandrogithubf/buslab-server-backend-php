<?php

namespace App\Topnode\BaseBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Abstract class to allow less code and better code reuse at the systema on
 * handling entity datas in CRUD.
 */
abstract class AbstractApiController extends Controller
{
    protected function responseFormError(
        FormErrorIterator $errors,
        string $message = '',
        int $status = 400
    ): JsonResponse {
        return $this->get('tn.utils.api.response')
            ->errorFromForm($errors, $message, $status)
        ;
    }

    protected function responseError(
        int $status = 500,
        string $message = '',
        array $details = [],
        array $headers = []
    ): JsonResponse {
        return $this->get('tn.utils.api.response')
            ->error($status, $message, $details, $headers)
        ;
    }

    protected function paginate($something): array
    {
        return $this->get('tn.utils.paginator.api')->paginate($something);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getDoctrine()->getManager();
    }

    protected function persist(object $entity): object
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    protected function response(
        ?int $status = null,
        $data = null,
        ?array $headers = []
    ): JsonResponse {
        return $this->get('tn.utils.api.response')
            ->response($status, $data, $headers)
        ;
    }

    protected function emptyResponse(
        array $headers = []
    ): JsonResponse {
        return $this->response(204, [], $headers);
    }
}
