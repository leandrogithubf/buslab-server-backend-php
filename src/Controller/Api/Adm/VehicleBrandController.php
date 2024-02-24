<?php

namespace App\Controller\Api\Adm;

use App\Entity\VehicleBrand;
use App\Entity\VehicleModel;
use App\Form\Api\Adm\VehicleBrandSearchTypeFactory;
use App\Form\Api\Adm\VehicleBrandTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/vehicle/brand",
 *     name="api_adm_vehicle_brand_"
 * )
 */
class VehicleBrandController extends AbstractApiController
{
    /**
     * API-063.
     *
     * @Route(
     *     "/list",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listAction(
        VehicleBrandSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(VehicleBrand::class)
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

            if (strlen($searchData['description']) > 0) {
                $qb
                    ->andWhere('e.description LIKE :description')
                    ->setParameter('description', '%' . $searchData['description'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-064.
     *
     * @Route(
     *     "/new",
     *     name="new",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function newAction(
        VehicleBrandTypeFactory $formFactory
    ): JsonResponse {
        $entity = new VehicleBrand();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * API-065.
     *
     * @Route(
     *     "/{identifier}/show",
     *     name="show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showAction(VehicleBrand $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     *  API-066.
     *
     * @Route(
     *     "/{identifier}/edit",
     *     name="edit",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function editAction(
        VehicleBrand $entity,
        VehicleBrandTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        return $this->emptyResponse();
    }

    /**
     * API-067.
     *
     * @Route(
     *     "/{identifier}/remove",
     *     name="remove",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function removeAction(VehicleBrand $entity): JsonResponse
    {
        if (count($entity->getVehicleModels()) > 0) {
            return $this->responseError(400, 'Esta marca não pode ser removida, pois possui modelos associados');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-068.
     *
     * @Route(
     *     "/{identifier}/models",
     *     name="models",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function listVehicleBrandModelsAction(
        VehicleBrand $entity,
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(VehicleModel::class)
            ->createQueryBuilder('e')
            ->andWhere('e.brand = :brand')
            ->setParameter('brand', $entity)
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
}
