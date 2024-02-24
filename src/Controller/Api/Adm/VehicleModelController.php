<?php

namespace App\Controller\Api\Adm;

use App\Entity\VehicleModel;
use App\Form\Api\Adm\VehicleModelSearchTypeFactory;
use App\Form\Api\Adm\VehicleModelTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/vehicle/model",
 *     name="api_adm_vehicle_model_"
 * )
 */
class VehicleModelController extends AbstractApiController
{
    /**
     * API-069.
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
        VehicleModelSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(VehicleModel::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.brand', 'brand')
            ->addOrderBy('e.description', 'ASC')
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
                        $qb->expr()->like('e.volume', ':search'),
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

            if (count($searchData['brand']) > 0) {
                $qb
                    ->andWhere('e.brand in (:brands)')
                    ->setParameter('brands', $searchData['brand'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-070.
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
        VehicleModelTypeFactory $formFactory
    ): JsonResponse {
        $entity = new VehicleModel();

        $form = $formFactory->setData($entity)->getFormHandled();
        if (is_null($entity->getEfficiency())) {
            $entity->setEfficiency(0.65);
        }

        if (is_null($entity->getAirFuelRatio())) {
            $entity->setAirFuelRatio(29);
        }

        if (is_null($entity->getFuelDensity())) {
            $entity->setFuelDensity(832);
        }

        if (is_null($entity->getVolume())) {
            $entity->setVolume(4.8);
        }

        if (is_null($entity->getEct())) {
            $entity->setEct(95.0);
        }

        if (is_null($entity->getIat())) {
            $entity->setIat(99.0);
        }

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
     * API-071.
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
    public function showAction(VehicleModel $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-072.
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
        VehicleModel $entity,
        VehicleModelTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (is_null($entity->getEfficiency())) {
            $entity->setEfficiency(0.65);
        }

        if (is_null($entity->getAirFuelRatio())) {
            $entity->setAirFuelRatio(29);
        }

        if (is_null($entity->getFuelDensity())) {
            $entity->setFuelDensity(832);
        }

        if (is_null($entity->getVolume())) {
            $entity->setVolume(4.8);
        }

        if (is_null($entity->getEct())) {
            $entity->setVolume(95.0);
        }

        if (is_null($entity->getIat())) {
            $entity->setVolume(99.0);
        }

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
     * API-073.
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
    public function removeAction(VehicleModel $entity): JsonResponse
    {
        if (count($entity->getVehicles()) > 0) {
            return $this->responseError(400, 'Este modelo não pode ser removido, pois possui veículos associados');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
