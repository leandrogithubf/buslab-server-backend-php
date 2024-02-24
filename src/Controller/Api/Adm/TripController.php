<?php

namespace App\Controller\Api\Adm;

use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Form\Api\Adm\TripAlternModalityTypeFactory;
use App\Form\Api\Adm\TripAlternStatusTypeFactory;
use App\Form\Api\Adm\TripTypeFactory;
use App\Form\Api\TripSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/trip",
 *     name="api_adm_trip_"
 * )
 */
class TripController extends AbstractApiController
{
    /**
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
        TripTypeFactory $formFactory,
        Request $request
    ): JsonResponse {
        $entity = new Trip();

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
     *         "_format"="json"
     *     }
     * )
     */
    public function showAction(ScheduleDate $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/{identifier}/checkpoints",
     *     name="checkpoints",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function checkpointsAction(Trip $entity): JsonResponse
    {
        return $this->response(200, $entity->getCheckpoints());
    }

    /**
     * @Route(
     *     "/{identifier}/occurrences",
     *     name="occurrences",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function occurrencesAction(Trip $entity): JsonResponse
    {
        return $this->response(200, $entity->getEvents());
    }

    /**
     * @Route(
     *     "/{identifier}/changeStatusAction",
     *     name="changeStatusAction",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function changeStatusAction(
        Trip $entity,
        TripAlternStatusTypeFactory $formFactory
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
     *     "/{identifier}/changeModalityAction",
     *     name="changeModalityAction",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function changeModalityAction(
        Trip $entity,
        TripAlternModalityTypeFactory $formFactory
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
        TripSearchTypeFactory $formFactory
    ): JsonResponse {
        $data = $formFactory->getFormHandled()->getData();
        $startsAt = null;
        $endsAt = null;

        $now = new \DateTime();
        if (isset($data['start']) && $data['start'] != null && $data['end'] === null) {
            $startsAt = $data['start'];
            $endsAt = (clone $now);
        }

        if (isset($data['start']) && $data['start'] === null && $data['end'] != null) {
            $startsAt = (clone $now);
            $endsAt = $data['end'];
        }

        if (isset($data['start']) && $data['start'] != null && $data['end'] != null) {
            $startsAt = $data['start'];
            $endsAt = $data['end'];
        }

        $qb = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->innerJoin(Schedule::class, 'schedule', 'WITH', 'e.schedule = schedule.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'schedule.line = line.id')
            ->innerJoin(Employee::class, 'driver', 'WITH', 'e.driver = driver.id')
            // ->innerJoin(Trip::class, 'trip', 'WITH', 'trip.scheduleDate = e.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('schedule.company = :company')->setParameter('company', $company);
        }

        if (!is_null($startsAt) && !is_null($endsAt)) {
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $endsAt)
            ;
        }

        if (isset($data['days']) && !is_null($data['days'])) {
            $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $now)
            ;
        }

        if (isset($data['sequence']) && count($data['sequence']) > 0 && !is_null($data['sequence'][0])) {
            $query = '';
            foreach ($data['sequence'] as $key => $date) {
                if ($key > 0) {
                    $query .= ' or ';
                }
                $query .= 'e.date between :date' . $key . 'start and :date' . $key . 'end';

                $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
            }

            $qb->andWhere($query);
        }

        if (isset($data['line']) && count($data['line']) > 0) {
            $qb
                ->andWhere('schedule.line = (:line)')
                ->setParameter('line', $data['line'])
            ;
        }

        if (isset($data['driver']) && count($data['driver']) > 0) {
            $qb
                ->andWhere('e.driver = (:employee)')
                ->setParameter('employee', $data['driver'])
            ;
        }

        if (isset($data['vehicle']) && count($data['vehicle']) > 0) {
            $qb
                ->andWhere('e.vehicle = (:vehicle)')
                ->setParameter('vehicle', $data['vehicle'])
            ;
        }

        if (isset($data['company']) && count($data['company']) > 0) {
            $qb
                ->andWhere('schedule.company = (:company)')
                ->setParameter('company', $data['company'])
            ;
        }

        return $this->response(200, $this->paginate($qb));
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
    public function removeAction(Trip $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/list-day",
     *     name="list-day",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listDayAction(
    ): JsonResponse {
        $date = new \DateTime();
        $date->modify('-1 day');

        $qb = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.starts_at > (:date)')
            ->setParameter('date', $date)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this->response(200, $qb);
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
        Trip $entity,
        TripTypeFactory $formFactory
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
     *     "/export",
     *     name="export",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function exportAction(
        TripSearchTypeFactory $formFactory
    ) {
        $data = $formFactory->getFormHandled()->getData();
        $startsAt = null;
        $endsAt = null;

        $now = new \DateTime();
        if (isset($data['start']) && $data['start'] != null && $data['end'] === null) {
            $startsAt = $data['start'];
            $endsAt = (clone $now);
        }

        if (isset($data['start']) && $data['start'] === null && $data['end'] != null) {
            $startsAt = (clone $now);
            $endsAt = $data['end'];
        }

        if (isset($data['start']) && $data['start'] != null && $data['end'] != null) {
            $startsAt = $data['start'];
            $endsAt = $data['end'];
        }

        $qb = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->innerJoin(Schedule::class, 'schedule', 'WITH', 'e.schedule = schedule.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'schedule.line = line.id')
            ->innerJoin(Employee::class, 'driver', 'WITH', 'e.driver = driver.id')
            // ->innerJoin(Trip::class, 'trip', 'WITH', 'trip.scheduleDate = e.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('schedule.company = :company')->setParameter('company', $company);
        }

        if (!is_null($startsAt) && !is_null($endsAt)) {
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $endsAt)
            ;
        }

        if (isset($data['days']) && !is_null($data['days'])) {
            $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $now)
            ;
        }

        if (isset($data['sequence']) && count($data['sequence']) > 0 && !is_null($data['sequence'][0])) {
            $query = '';
            foreach ($data['sequence'] as $key => $date) {
                if ($key > 0) {
                    $query .= ' or ';
                }
                $query .= 'e.date between :date' . $key . 'start and :date' . $key . 'end';

                $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
            }

            $qb->andWhere($query);
        }

        if (isset($data['line']) && count($data['line']) > 0) {
            $qb
                ->andWhere('schedule.line = (:line)')
                ->setParameter('line', $data['line'])
            ;
        }

        if (isset($data['driver']) && count($data['driver']) > 0) {
            $qb
                ->andWhere('e.driver = (:employee)')
                ->setParameter('employee', $data['driver'])
            ;
        }

        if (isset($data['vehicle']) && count($data['vehicle']) > 0) {
            $qb
                ->andWhere('e.vehicle = (:vehicle)')
                ->setParameter('vehicle', $data['vehicle'])
            ;
        }

        if (isset($data['company']) && count($data['company']) > 0) {
            $qb
                ->andWhere('schedule.company = (:company)')
                ->setParameter('company', $data['company'])
            ;
        }

        $qb = $qb->getQuery()->getResult();

        $viagensCsv = fopen('viagens.csv', 'w');

        fputcsv($viagensCsv, ['Viagens no período']);
        fputcsv($viagensCsv, [
            'Tabela',
            'Linha',
            'Sentido',
            'Motorista',
            'Prefixo',
            'Data',
            'Saída Prevista',
            'Saída Realizada',
            'Diferença',
            'Chegada Prevista',
            'Chegada Realizada',
            'Diferença',
        ]);
        fputcsv($viagensCsv, []);

        if ($qb) {
            foreach ($qb as $scheduleDate) {
                $timeStart1 = (clone $scheduleDate->getDate())->setTime($scheduleDate->getSchedule()->getStartsAt()->format('H'), $scheduleDate->getSchedule()->getStartsAt()->format('i'), 0);
                $timeStart2 = $scheduleDate->getTrip() ? $scheduleDate->getTrip()->getStartsAt() : null;
                $timeEnd1 = (clone $scheduleDate->getDate())->setTime($scheduleDate->getSchedule()->getEndsAt()->format('H'), $scheduleDate->getSchedule()->getEndsAt()->format('i'), 0);
                $timeEnd2 = $scheduleDate->getTrip() ? $scheduleDate->getTrip()->getEndsAt() : null;

                if ($scheduleDate->getSchedule()->getLine()->getDirection() == 'GOING') {
                    $direction = 'Ida';
                } elseif ($scheduleDate->getSchedule()->getLine()->getDirection() == 'RETURN') {
                    $direction = 'Volta';
                } else {
                    $direction = 'Circular';
                }

                $end = '-';
                if ($scheduleDate->getTrip()) {
                    if (is_null($scheduleDate->getTrip()->getEndsAt())) {
                        $end = 'Em viagem';
                    } else {
                        $end = $scheduleDate->getTrip()->getEndsAt()->format('H:i:s');
                    }
                }

                fputcsv($viagensCsv, [
                    $scheduleDate->getSchedule()->getTableCode(),
                    $scheduleDate->getSchedule()->getLine()->getDescription(),
                    $direction,
                    $scheduleDate->getDriver()->getName(),
                    $scheduleDate->getVehicle()->getPrefix(),
                    $scheduleDate->getDate()->format('d/m/Y'),
                    $scheduleDate->getSchedule()->getStartsAt()->format('H:i:s'),
                    is_null($scheduleDate->getTrip()) ? '-' : $scheduleDate->getTrip()->getStartsAt()->format('H:i:s'),

                    $scheduleDate->getTrip() ? ($scheduleDate->getSchedule()->getStartsAt() < $scheduleDate->getTrip()->getStartsAt() ? '- ' . ($timeStart2->diff($timeStart1))->format('%h:%i:%s') : ($timeStart2->diff($timeStart1))->format('%h:%i:%s')) : '-',
                    $scheduleDate->getSchedule()->getEndsAt()->format('H:i:s'),
                    $scheduleDate->getTrip() ? ($scheduleDate->getTrip()->getEndsAt() ? $scheduleDate->getTrip()->getEndsAt()->format('H:i:s') : '-') : '-',
                    $end,
                ]);
            }
        }
        fputcsv($viagensCsv, []);

        fclose($viagensCsv);

        return $this->file('viagens.csv');
    }

    /**
     * @Route(
     *     "/list-form",
     *     name="list_form",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listFormAction(
    ): JsonResponse {
        $now = (new \DateTime())->setTime(0, 0, 0);
        $start = $now->setDate($now->format('Y'), $now->format('m'), 1);

        $qb = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.starts_at >= :start')
            ->setParameter('start', $start)
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        return $this->response(200, $this->paginate($qb));
    }
}
