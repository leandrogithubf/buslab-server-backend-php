<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\Parameter;
use App\Entity\ParameterConfiguration;
use App\Form\Api\Adm\ParameterConfigurationBatchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/company/{identifier}/parameter-configuration",
 *     name="api_adm_parameter_configuration_",
 *     requirements={
 *         "_format"="json",
 *         "identifier"="[\w\-\_]{15}",
 *     }
 * )
 */
class CompanyParameterConfigurationController extends AbstractApiController
{
    /**
     * API-090.
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
        ApiSearchFormTypeFactory $formFactory,
        Company $company
    ): JsonResponse {
        $list = $this->getEntityManager()
            ->getRepository(Parameter::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($list as $entity) {
            $result[$entity->getIdentifier()] = [
                'identifier' => $entity->getIdentifier(),
                'description' => $entity->getDescription(),
                'maxAllowed' => null,
                'minAllowed' => null,
            ];
        }

        $list = $this->getEntityManager()
            ->getRepository(ParameterConfiguration::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult()
        ;

        foreach ($list as $entity) {
            $paramIdentifier = $entity->getParameter()->getIdentifier();
            $result[$paramIdentifier]['maxAllowed'] = $entity->getMaxAllowed();
            $result[$paramIdentifier]['minAllowed'] = $entity->getMinAllowed();
        }

        return $this->response(200, $result);
    }

    /**
     * API-091.
     *
     * @Route(
     *     "/update",
     *     name="update",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function updateAction(
        ParameterConfigurationBatchTypeFactory $formFactory,
        Company $company
    ): JsonResponse {
        $list = $this->getEntityManager()
            ->getRepository(ParameterConfiguration::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult()
        ;

        $form = $formFactory->setData([
            'values' => $list,
        ])->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        foreach ($form->getData()['values'] as $row) {
            $row->setCompany($company);
            $this->persist($row);
        }

        return $this->emptyResponse();
    }
}
