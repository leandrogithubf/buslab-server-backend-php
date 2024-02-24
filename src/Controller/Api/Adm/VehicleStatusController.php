<?php

namespace App\Controller\Api\Adm;

use App\Entity\VehicleStatus;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/vehicle/status",
 *     name="api_adm_vehicle_status_"
 * )
 */
class VehicleStatusController extends AbstractApiController
{
    /**
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
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(VehicleStatus::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/{id}/show",
     *     name="show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showAction(VehicleStatus $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }
}
