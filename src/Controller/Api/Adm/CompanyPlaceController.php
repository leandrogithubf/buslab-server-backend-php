<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\CompanyPlace;
use App\Form\Api\Adm\CompanyPlaceTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/company/{company_identifier}/place",
 *     name="api_adm_company_place_"
 * )
 * @ParamConverter("company", options={
 *     "mapping"={"company_identifier"="identifier"}
 * })
 */
class CompanyPlaceController extends AbstractApiController
{
    /**
     * API-092.
     *
     * @Route(
     *     "/list",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function listAction(Company $company): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(CompanyPlace::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $company)
            ->orderBy('e.id', 'DESC')
        ;

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-093.
     *
     * @Route(
     *     "/new",
     *     name="new",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function newAction(
        CompanyPlaceTypeFactory $formFactory,
        Company $company,
        Request $request
    ): JsonResponse {
        $entity = (new CompanyPlace())->setCompany($company);

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
     * API-094.
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
    public function showAction(
        CompanyPlace $entity,
        Company $company
    ): JsonResponse {
        if ($entity->getCompany()->getId() !== $company->getId()) {
            return $this->responseError(404);
        }

        return $this->response(200, $entity);
    }

    /**
     * API-095.
     *
     * @Route(
     *     "/{identifier}/edit",
     *     name="edit",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function editAction(
        CompanyPlace $entity,
        Company $company,
        CompanyPlaceTypeFactory $formFactory
    ): JsonResponse {
        if ($entity->getCompany()->getId() !== $company->getId()) {
            return $this->responseError(404);
        }

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
     * API-096.
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
    public function removeAction(
        CompanyPlace $entity,
        Company $company
    ): JsonResponse {
        if ($entity->getCompany()->getId() !== $company->getId()) {
            return $this->responseError(404);
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
