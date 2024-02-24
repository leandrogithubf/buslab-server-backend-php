<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EmployeeModality;
use App\Entity\Event;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Form\Api\Adm\EmployeeSearchTypeFactory;
use App\Form\Api\Adm\EmployeeTypeFactory;
use App\Form\Api\Adm\ImportTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/employee",
 *     name="api_adm_employee_"
 * )
 */
class EmployeeController extends AbstractApiController
{
    /**
     * API-077.
     *
     * @Route(
     *     "/list/{modality}",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *         "modality"="any|driver|collector"
     *     }
     * )
     */
    public function listAction(
        EmployeeSearchTypeFactory $formFactory,
        string $modality = 'any'
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.company', 'company')
            ->leftJoin('e.modality', 'modality')
            ->addOrderBy('e.code', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['name']) > 0) {
                $qb
                    ->andWhere('e.name LIKE :names')
                    ->setParameter('names', '%' . $searchData['name'] . '%')
                ;
            }

            if (count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }

            if (strlen($searchData['code']) > 0) {
                $qb
                    ->andWhere('e.code in (:codes)')
                    ->setParameter('codes', $searchData['code'])
                ;
            }

            if (count($searchData['modality']) > 0) {
                $qb
                    ->andWhere('e.modality in (:modalitys)')
                    ->setParameter('modalitys', $searchData['modality'])
                ;
            }
        }

        if ($modality === 'driver') {
            $qb
                ->andWhere('e.modality = :modality')
                ->setParameter('modality', EmployeeModality::DRIVER)
            ;
        } elseif ($modality === 'collector') {
            $qb
                ->andWhere('e.modality = :modality')
                ->setParameter('modality', EmployeeModality::COLLECTOR)
            ;
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-078.
     *
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
        EmployeeTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Employee();

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
     * API-079.
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
    public function showAction(Employee $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-080.
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
        Employee $entity,
        EmployeeTypeFactory $formFactory
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
     * API-081.
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
    public function removeAction(Employee $entity): JsonResponse
    {
        $schedule = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.employee = (:employee)')
            ->setParameter('employee', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($schedule) > 0) {
            return $this->responseError(400, 'Este funcionário não pode ser removido pois possui uma escala vinculada.');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-082.
     *
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

        $companys = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $modalitys = $this->getEntityManager()
            ->getRepository(EmployeeModality::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        foreach ($csv as $value) {
            $employee = new Employee();
            $employee->setName($value[0]);
            $employee->setCode($value[1]);

            foreach ($companys as $company) {
                if (strtoupper($value[2]) == strtoupper($company->getDescription())) {
                    $employee->setCompany($company);
                }
            }

            foreach ($modalitys as $modality) {
                if (strtoupper($value[3]) == strtoupper($modality->getDescription())) {
                    $employee->setModality($modality);
                }
            }

            $employee->setCnh($value[4]);
            $expiration = date_create_from_format('d/m/Y', '1/' . $value[5])->setTime(0, 0, 0);
            $employee->setCnhExpiration($expiration);
            $employee->setCellphone($value[6]);

            $this->persist($employee);
        }

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/remove-cascade",
     *     name="remove_cascade",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
               "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function removeCascadeAction(Employee $entity): JsonResponse
    {
        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver = (:employee) or e.collector = (:employee)')
            ->setParameter('employee', $entity)
            ->getQuery()
            ->getResult()
        ;

        $schedules = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver = (:employee) or e.collector = (:employee)')
            ->setParameter('employee', $entity)
            ->getQuery()
            ->getResult()
        ;

        $scheduleDates = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver = (:employee) or e.collector = (:employee)')
            ->setParameter('employee', $entity)
            ->getQuery()
            ->getResult()
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver = (:employee) or e.collector = (:employee)')
            ->setParameter('employee', $entity)
            ->getQuery()
            ->getResult()
        ;

        $all = [$events, $trips, $scheduleDates, $schedules];
        if (count($trips) > 0 || count($events) > 0 || count($scheduleDates) > 0 || count($schedules) > 0) {
            foreach ($all as $key => $itens) {
                if (!is_null($itens) || count($itens) > 0) {
                    foreach ($itens as $key => $item) {
                        if ($item->getDriver() == $entity) {
                            $this->persist($item->setIsActive(false));
                        } else {
                            $this->persist($item->setCollector(null));
                        }
                    }
                }
            }
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
