<?php

namespace App\Controller\Api\Adm;

use App\Entity\FuelQuote;
use App\Form\Api\Adm\FuelQuoteSearchTypeFactory;
use App\Form\Api\Adm\FuelQuoteTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/fuel-quote",
 *     name="api_adm_fuel_quote_"
 * )
 */
class FuelQuoteController extends AbstractApiController
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
        FuelQuoteSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(FuelQuote::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.company', 'company')
            ->addOrderBy('e.date', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $form = $formFactory->getFormHandled();
        $searchData = $form->getData();
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if ($searchData['date']) {
                $qb
                    ->andWhere('e.date LIKE :date')
                    ->setParameter('date', $searchData['date'])
                ;
            }

            if (count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/new",
     *     name="new",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function newAction(
        FuelQuoteTypeFactory $formFactory
    ): JsonResponse {
        $entity = new FuelQuote();

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
    public function showAction(FuelQuote $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
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
        FuelQuote $entity,
        FuelQuoteTypeFactory $formFactory
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
    public function removeAction(FuelQuote $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
