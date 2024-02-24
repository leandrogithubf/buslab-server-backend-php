<?php

namespace App\Controller\Api\Adm;

use App\Entity\CellphoneNumber;
use App\Entity\Obd;
use App\Form\Api\Adm\CellphoneNumberSearchTypeFactory;
use App\Form\Api\Adm\CellphoneNumberTypeFactory;
use App\Form\Api\Adm\ImportTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/cellphone",
 *     name="api_adm_cellphone_"
 * )
 */
class CellphoneNumberController extends AbstractApiController
{
    /**
     * API-058.
     *
     * @Route(
     *     "/list/{status}",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *         "status"="free|all"
     *     }
     * )
     */
    public function listAction(
        CellphoneNumberSearchTypeFactory $formFactory,
        string $status = 'all'
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(CellphoneNumber::class)
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
        ;

        if ($status === 'free') {
            $qb
            ->leftJoin(Obd::class, 'obd', 'WITH', 'obd.cellphoneNumber = e.id')
            ->andWhere('obd is null')
            ;
        }
        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $withdraw = ['(', ') ', '-'];
            $searchData['number'] = str_replace($withdraw, '', $searchData['number']);

            if (strlen($searchData['number']) > 0) {
                $qb
                    ->andWhere('e.number LIKE :number')
                    ->setParameter('number', '%' . $searchData['number'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-059.
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
        CellphoneNumberTypeFactory $formFactory
    ): JsonResponse {
        $entity = new CellphoneNumber();

        $form = $formFactory->setData($entity)->getFormHandled();
        $entity->setStatus(false);
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
     * API-060.
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
    public function showAction(CellphoneNumber $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-061.
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
        CellphoneNumber $entity,
        CellphoneNumberTypeFactory $formFactory
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
     * API-062.
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
    public function removeAction(CellphoneNumber $entity): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(Obd::class)
            ->createQueryBuilder('e')
            ->andWhere('e.cellphoneNumber = (:cellphone)')
            ->setParameter('cellphone', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($qb) > 0) {
            return $this->responseError(400, 'Este número de telefone não pode ser removido, pois possui obds associados');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/import",
     *     name="import",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function importAction(
        ImportTypeFactory $formFactory,
        Request $request
    ): JsonResponse {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        set_time_limit(300);

        $form = $formFactory->getFormHandled();
        $filePath = $form->getData()['file']->getRealPath();
        $csv = array_map('str_getcsv', file($filePath));
        foreach ($csv as $value) {
            $cellphone = new CellphoneNumber();
            $cellphone->setNumber($value[0]);
            $cellphone->setStatus(false);

            $this->persist($cellphone);
        }

        return $this->emptyResponse();
    }
}
