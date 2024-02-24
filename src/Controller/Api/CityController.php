<?php

namespace App\Controller\Api;

use App\Entity\City;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/city",
 *     name="api_city_"
 * )
 */
class CityController extends AbstractApiController
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
    public function showAction(City $entity)
    {
        return $this->forward('App\Controller\Api\GeolocationController::showCityAction', [
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
    ) {
        return $this->forward('App\Controller\Api\GeolocationController::listCityAction', [
            'formFactory' => $formFactory,
        ]);
    }
}
