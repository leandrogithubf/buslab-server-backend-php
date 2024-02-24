<?php

namespace App\Controller\Api\Adm;

use App\Entity\EmployeeModality;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/employee-modality",
 *     name="api_adm_employee_modality_"
 * )
 */
class EmployeeModalityController extends AbstractApiController
{
    /**
     * @deprecated
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
    public function showAction(EmployeeModality $entity): JsonResponse
    {
        return $this->forward('App\Controller\Api\EmployeeController::showModalityAction', [
            'entity' => $entity,
        ]);
    }

    /**
     * @deprecated
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
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        return $this->forward('App\Controller\Api\EmployeeController::listModalityAction', [
            'formFactory' => $formFactory,
        ]);
    }
}
