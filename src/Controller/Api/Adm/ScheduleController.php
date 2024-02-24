<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\Line;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Form\Api\Adm\ImportTypeFactory;
use App\Form\Api\Adm\ScheduleSearchTypeFactory;
use App\Form\Api\Adm\ScheduleTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/schedule",
 *     name="api_adm_\schedule_"
 * )
 */
class ScheduleController extends AbstractApiController
{
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

        $vehicles = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $drivers = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $lines = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $companys = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        foreach ($csv as $key => $value) {
            $escala = new Schedule();
            $direction = null;

            $escala->setTableCode($value[0]);
            $escala->setSequence($value[1]);
            $escala->setStartsAt(date_create_from_format('H:i', $value[2]));
            $escala->setEndsAt(date_create_from_format('H:i', $value[3]));
            $escala->setDataValidity(date_create_from_format('d/m/Y', $value[4])->setTime(0, 0, 0));

            if (!in_array(strtoupper($value[5]), ['INÍCIO DE OPERAÇÂO', 'RESERVADO', 'DESLOCAMENTO', 'VIAGEM', 'ENCERRAMENTO DE OPERAÇÃO'])) {
                return $this->responseError(400, 'Erro de modalidade encontrado na linha ' . ($key + 1) . '.');
            }
            if (strtoupper($value[5]) == 'INÍCIO DE OPERAÇÂO') {
                $escala->setModality('STARTING_OPERATION');
            }
            if (strtoupper($value[5]) == 'RESERVADO') {
                $escala->setModality('RESERVED');
            }

            if (strtoupper($value[5]) == 'DESLOCAMENTO') {
                $escala->setModality('MOVEMENT');
            }

            if (strtoupper($value[5]) == 'VIAGEM') {
                $escala->setModality('TRIP');
            }
            if (strtoupper($value[5]) == 'ENCERRAMENTO DE OPERAÇÃO') {
                $escala->setModality('CLOSING_OPERATION');
            }

            if (!in_array(strtoupper($value[6]), ['DIA ÚTIL', 'SÁBADO', 'DOMINGO'])) {
                return $this->responseError(400, 'Erro de intervalo encontrado na linha ' . ($key + 1) . '.');
            }
            if (strtoupper($value[6]) == 'DIA ÚTIL') {
                $escala->setWeekInterval('WEEKDAY');
            }

            if (strtoupper($value[6]) == 'SÁBADO') {
                $escala->setWeekInterval('SATURDAY');
            }

            if (strtoupper($value[6]) == 'DOMINGO') {
                $escala->setWeekInterval('SUNDAY');
            }

            if (!in_array(strtoupper($value[8]), ['IDA', 'VOLTA', 'CIRCULAR'])) {
                return $this->responseError(400, 'Erro de sentido encontrado na linha ' . ($key + 1) . '.');
            }
            if (strtoupper($value[8]) == 'IDA') {
                $direction = 'GOING';
            }
            if (strtoupper($value[8]) == 'VOLTA') {
                $direction = 'RETURN';
            }
            if (strtoupper($value[8]) == 'CIRCULAR') {
                $direction = 'CIRCULATE';
            }

            foreach ($lines as $line) {
                if (strtoupper($value[7]) == strtoupper($line->getCode()) && $direction == $line->getDirection()) {
                    $escala->setLine($line);
                    break;
                }
            }
            if (is_null($escala->getLine())) {
                return $this->responseError(400, 'Linha não encontrada na linha ' . ($key + 1) . '.');
            }

            foreach ($vehicles as $car) {
                if (strtoupper($value[9]) == strtoupper($car->getPrefix())) {
                    $escala->setVehicle($car);
                    break;
                }
            }
            if (is_null($escala->getVehicle())) {
                return $this->responseError(400, 'Veículo não encontrado na linha ' . ($key + 1) . '.');
            }

            foreach ($drivers as $driver) {
                if (strtoupper($value[10]) == strtoupper($driver->getCode())) {
                    $escala->setDriver($driver);
                }
                if ($value[11] != '-') {
                    if (strtoupper($value[11]) == strtoupper($driver->getCode())) {
                        $escala->setCollector($driver);
                    }
                }
            }

            $escala->setDescription($value[12]);

            foreach ($companys as $company) {
                if (strtoupper($value[13]) == strtoupper($company->getDescription())) {
                    $escala->setCompany($company);
                    continue;
                }
            }

            if (is_null($escala->getCollector()) && $value[11] != '-') {
                return $this->responseError(400, 'Cobrador não encontrado na linha ' . ($key + 1) . '.');
            }
            if (is_null($escala->getDriver())) {
                return $this->responseError(400, 'Motorista não encontrado na linha ' . ($key + 1) . '.');
            }
            if (is_null($escala->getCompany())) {
                return $this->responseError(400, 'Empresa não encontrada na linha ' . ($key + 1) . '.');
            }

            $this->persist($escala);

            $escalaDate = new ScheduleDate();
            $escalaDate->setDriver($escala->getDriver());
            $escalaDate->setCollector($escala->getCollector());
            $escalaDate->setVehicle($escala->getVehicle());
            $escalaDate->setDate($escala->getDataValidity());
            $escalaDate->setSchedule($escala);

            $this->persist($escalaDate);
        }

        return $this->emptyResponse();
    }

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
        ScheduleSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.line', 'line')
            ->leftJoin('e.company', 'company')
            ->leftJoin('e.vehicle', 'vehicle')
            ->orderBy('e.tableCode', 'DESC')
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

            if (count($searchData['vehicle']) > 0) {
                $qb
                    ->andWhere('e.vehicle in (:vehicle)')
                    ->setParameter('vehicle', $searchData['vehicle'])
                ;
            }

            if (count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
            if (count($searchData['line']) > 0) {
                $qb
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
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
        ScheduleTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Schedule();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if (strlen($entity->getDescription()) === 0) {
            $entity->setDescription(
                $entity->getTableCode()
                . ' - ' . $entity->getLine()->getDescription()
                . ' - ' . $entity->getLine()->getDirection($asHuman = true)
            );
        }

        $em = $this->getEntityManager();

        $entity->emptyDates();
        $this->persist($entity);

        foreach ($form['dates']->getData() as $scheduleDate) {
            $scheduleDate->setSchedule($entity);
            $em->persist($scheduleDate);

            $entity->addDate($scheduleDate);
        }

        $em->persist($entity);
        $em->flush();

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
    public function showAction(Schedule $entity): JsonResponse
    {
        return $this->response(200, $entity);
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
    public function removeAction(Schedule $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
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
        Schedule $entity,
        ScheduleTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $dates = $entity->getDates();
        if (count($dates) > 0) {
            $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.scheduleDate IN (:dates)')
            ->setParameter('dates', $dates)
            ->getQuery()
            ->getResult()
        ;

            if (count($trips) > 0) {
                return $this->responseError(400, 'Esta escala não pode ser editada, pois possui viagens já iniciadas');
            }
        }

        $entity->emptyDates();
        $this->persist($entity);

        $em = $this->getEntityManager();

        foreach ($form['dates']->getData() as $scheduleDate) {
            $scheduleDate->setSchedule($entity);
            $em->persist($scheduleDate);

            $entity->addDate($scheduleDate);
        }

        $em->persist($entity);
        $em->flush();

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/remove-cascade",
     *     name="remove_cascade",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function removeCascadeAction(ScheduleDate $entity): JsonResponse
    {
        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver = (:scheduleDate)')
            ->setParameter('scheduleDate', $entity)
            ->getQuery()
            ->getResult()
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.trip IN (:trip)')
            ->setParameter('trip', $trips)
            ->getQuery()
            ->getResult()
        ;

        $all = [$events, $trips];
        if (count($trips) > 0 || count($events) > 0) {
            foreach ($all as $key => $itens) {
                if (!is_null($itens) || count($itens) > 0) {
                    foreach ($itens as $key => $item) {
                        $this->persist($item->setIsActive(false));
                    }
                }
            }
        }

        $this->persist($entity->setIsActive(false));
        $this->persist($entity->getSchedule()->setIsActive(false));

        return $this->emptyResponse();
    }
}
