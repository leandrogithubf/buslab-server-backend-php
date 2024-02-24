<?php

namespace App\Controller\Api;

use App\Entity\EventCategory;
use App\Entity\EventModality;
use App\Entity\EventStatus;
use App\Form\Api\EventCategorySearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/event",
 *     name="api_event_"
 * )
 */
class EventController extends AbstractApiController
{
    /**
     * API-022.
     *
     * @Route(
     *     "/category/list",
     *     name="category_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listCategoryAction(
        EventCategorySearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            ->orderBy('e.description', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.description', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }

            if (is_object($searchData['sector'])) {
                $qb
                    ->andWhere('e.sector = :sector')
                    ->setParameter('sector', $searchData['sector'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-023.
     *
     * @Route(
     *     "/category/{identifier}/show",
     *     name="category_show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showCategoryAction(EventCategory $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-024.
     *
     * @Route(
     *     "/modality/list",
     *     name="modality_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listModalityAction(
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->orderBy('e.description', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.description', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-025.
     *
     * @Route(
     *     "/modality/{identifier}/show",
     *     name="modality_show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showModalityAction(EventModality $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-026.
     *
     * @Route(
     *     "/status/list",
     *     name="status_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listStatusAction(
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(EventStatus::class)
            ->createQueryBuilder('e')
            ->orderBy('e.description', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.description', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-027.
     *
     * @Route(
     *     "/status/{identifier}/show",
     *     name="status_show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showStatusAction(EventStatus $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }
}
