<?php

namespace App\Controller\Api;

use App\Entity\Employee;
use App\Entity\EmployeeModality;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\EventModality;
use App\Entity\Line;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Form\Api\Adm\TelemetryOccurrenceSearchTypeFactory;
use App\Form\Api\Adm\TelemetrySearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/telemetry",
 *     name="api_telemetry_"
 * )
 */
class TelemetryController extends AbstractApiController
{
    /**
     *Gráfico-1-Ocorrencia.
     *
     * @Route(
     *     "/event-frequency",
     *     name="event_frequency",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function eventFrequencyAction(TelemetryOccurrenceSearchTypeFactory $formFactory): JsonResponse
    {
        // Freq.de ocorrências em todas as viagens do período

        $result = $this->getFrequencyResult($formFactory);
        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getFrequencyResult(TelemetryOccurrenceSearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->innerJoin(EventCategory::class, 'category', 'WITH', 'e.category = category.id')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'vehicle.id = e.vehicle')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.start between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', $searchData['direction'])
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('vehicle.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }

            if (isset($searchData['sector']) && count($searchData['sector']) > 0) {
                $list
                    ->andWhere('category.sector in (:sector)')
                    ->setParameter('sector', $searchData['sector'])
                ;
            }

            if (isset($searchData['occurrenceType']) && count($searchData['occurrenceType']) > 0) {
                $list
                    ->andWhere('e.category in (:occurrenceType)')
                    ->setParameter('occurrenceType', $searchData['occurrenceType'])
                ;
            }
        }

        $list = $list->getQuery()->getResult();
        $eventCategory = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        $position = 0;
        foreach ($eventCategory as $category) {
            $qtt = 0;

            foreach ($list as $event) {
                if ($event->getCategory()->getDescription() == $category->getDescription()) {
                    ++$qtt;
                }
            }

            if ($qtt > 0) {
                $result[] = [
                    'description' => $category->getDescription(),
                    'qtt' => $qtt,
                ];
            }

            ++$position;
        }

        return $result;
    }

    /**
     *Gráfico-2-Ocorrencia.
     *
     * @Route(
     *     "/total-event-by-date",
     *     name="total_event_by_date",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function totalEventByDateAction(TelemetryOccurrenceSearchTypeFactory $formFactory): JsonResponse
    {
        // Total de ocorrência no período

        $result = $this->getEventDateResult($formFactory);
        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getEventDateResult(TelemetryOccurrenceSearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->innerJoin(EventCategory::class, 'category', 'WITH', 'e.category = category.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->andWhere('e.modality = :modality')
            ->setParameter('modality', EventModality::OCCURRENCE)
            ->addOrderBy('e.id', 'DESC')
        ;

        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }
            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.start between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('line.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }

            if (isset($searchData['sector']) && count($searchData['sector']) > 0) {
                $list
                    ->andWhere('category.sector in (:sector)')
                    ->setParameter('sector', $searchData['sector'])
                ;
            }

            if (isset($searchData['occurrenceType']) && count($searchData['occurrenceType']) > 0) {
                $list
                    ->andWhere('e.category in (:occurrenceType)')
                    ->setParameter('occurrenceType', $searchData['occurrenceType'])
                ;
            }
        }

        $list = $list->getQuery()->getResult();
        $result = [];
        if (!isset($searchData['sequence']) || count($searchData['sequence']) <= 0) {
            while ($startsAt < $endsAt) {
                $qtt = 0;
                foreach ($list as $event) {
                    if ($event->getStart()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtt;
                    }
                }

                $result[] = [
                    'description' => $startsAt->format('d/m'),
                    'qtt' => $qtt,
                ];

                $startsAt->add(new \DateInterval('P1D'));
            }
        } else {
            $startsAts = $searchData['sequence'];
            foreach ($startsAts as $startsAt) {
                $qtt = 0;
                foreach ($list as $event) {
                    if ($event->getStart()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtt;
                    }
                }

                $result[] = [
                    'description' => $startsAt->format('d/m'),
                    'qtt' => $qtt,
                ];

                // $startsAt->add(new \DateInterval('P1D'));
            }
        }

        return $result;
    }

    /**
     *Gráfico-1-Consumo.
     *
     * @Route(
     *     "/fuel-consumption",
     *     name="fuel_consumption",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function fuelConsumptionAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        // Melhores e piores consumos

        $result = $this->getFuelResult($formFactory);

        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getFuelResult(TelemetrySearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.starts_at between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }
        $list = $list->getQuery()->getResult();
        $result = [];
        $day = 0;
        if (!isset($searchData['sequence']) || count($searchData['sequence']) <= 0) {
            while ($startsAt <= $endsAt) {
                $result[] = [
                'description' => $startsAt->format('d/m'),
                'best' => 0,
                'worst' => 0,
                'average' => 0,
            ];
                foreach ($list as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        $report = $trip->getReport();
                        if ($report) {
                            if ($result[$day]['best'] < $report->getConsumption()) {
                                $result[$day]['best'] = $report->getConsumption();
                            }
                            if ($result[$day]['worst'] == 0 || $result[$day]['worst'] > $report->getConsumption()) {
                                $result[$day]['worst'] = $report->getConsumption();
                            }
                            if ($result[$day]['average'] == 0) {
                                $result[$day]['average'] = $report->getConsumption();
                                continue;
                            }

                            $result[$day]['average'] = ($result[$day]['average'] + $report->getConsumption()) / 2;
                        }
                    }
                }

                ++$day;
                $startsAt->add(new \DateInterval('P1D'));
            }
        } else {
            $startsAts = $searchData['sequence'];
            foreach ($startsAts as $startsAt) {
                $result[] = [
                    'description' => $startsAt->format('d/m'),
                    'best' => 0,
                    'worst' => 0,
                    'average' => 0,
                ];
                foreach ($list as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        $report = $trip->getReport();
                        if ($report) {
                            if ($result[$day]['best'] < $report->getConsumption()) {
                                $result[$day]['best'] = $report->getConsumption();
                            }
                            if ($result[$day]['worst'] == 0 || $result[$day]['worst'] > $report->getConsumption()) {
                                $result[$day]['worst'] = $report->getConsumption();
                            }
                            if ($result[$day]['average'] == 0) {
                                $result[$day]['average'] = $report->getConsumption();
                                continue;
                            }

                            $result[$day]['average'] = ($result[$day]['average'] + $report->getConsumption()) / 2;
                        }
                    }
                }

                ++$day;
            }
        }

        return $result;
    }

    /**
     *Gráfico-2-Consumo.
     *
     * @Route(
     *     "/consumption-time",
     *     name="consumption_time",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function consumptionTimeAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        // Relação da média do consumo/hora do períodos

        $result = $this->getConsumptionTimeResult($formFactory);

        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getConsumptionTimeResult(TelemetrySearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;
        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.starts_at between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }
        $list = $list->getQuery()->getResult();
        $result = [];
        $day = 0;
        if (!isset($searchData['sequence']) || count($searchData['sequence']) <= 0) {
            while ($startsAt <= $endsAt) {
                $trips = [];
                foreach ($list as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        array_push($trips, $trip);
                    }
                }

                $interval = 0;
                $averageConsumption = 0;
                $times = [];
                foreach ($trips as $trip) {
                    if ($trip->getReport() && $trip->getReport()->getConsumption()) {
                        $averageConsumption += $trip->getReport()->getConsumption();
                    }
                }

                while ($interval <= 22) {
                    $qtd = 0;
                    $tripsAverages = 0;

                    foreach ($trips as $trip) {
                        if (($trip->getStartsAt() >= (clone $startsAt)->modify('+' . ($interval) . ' hours')) && ($trip->getStartsAt() < (clone $startsAt)->modify('+' . ($interval + 2) . ' hours'))) {
                            if ($trip->getReport() && $trip->getReport()->getConsumption()) {
                                $tripsAverages += $trip->getReport()->getConsumption();
                            }
                            ++$qtd;
                        }
                    }
                    if ($qtd > 0) {
                        $tripsAverages = $tripsAverages / $qtd++;
                    } else {
                        $tripsAverages = 0;
                    }

                    array_push($times, [$interval . ':00:00', $tripsAverages]);
                    $interval += 2;
                }
                $average = 0;
                if (count($trips) > 0) {
                    $average = $averageConsumption / count($trips);
                }
                $result[] = [
                    'description' => $startsAt->format('d/m'),
                    'average' => $average,
                    'times' => $times,
                ];
                $startsAt->add(new \DateInterval('P1D'));
            }
        } else {
            $startsAts = $searchData['sequence'];
            foreach ($startsAts as $startsAt) {
                $trips = [];
                foreach ($list as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        array_push($trips, $trip);
                    }
                }

                $interval = 0;
                $averageConsumption = 0;
                $times = [];
                foreach ($trips as $trip) {
                    if ($trip->getReport() && $trip->getReport()->getConsumption()) {
                        $averageConsumption += $trip->getReport()->getConsumption();
                    }
                }

                while ($interval <= 22) {
                    $qtd = 0;
                    $tripsAverages = 0;

                    foreach ($trips as $trip) {
                        if (($trip->getStartsAt() >= $startsAt->modify('+' + ($interval) + ' hours')) && ($trip->getStartsAt() < (clone $startsAt)->modify('+' + ($interval + 2) + ' hours'))) {
                            if ($trip->getReport() && $trip->getReport()->getConsumption()) {
                                $tripsAverages += $trip->getReport()->getConsumption();
                            }
                            ++$qtd;
                        }
                    }

                    if ($qtd > 0) {
                        $tripsAverages = $tripsAverages / $qtd++;
                    } else {
                        $tripsAverages = 0;
                    }

                    array_push($times, [$interval . ':00:00', $tripsAverages]);
                    $interval += 2;
                }
                $average = 0;
                if (count($trips) > 0) {
                    $average = $averageConsumption / count($trips);
                }

                $result[] = [
                    'description' => $startsAt->format('d/m'),
                    'average' => $average,
                    'times' => $times,
                ];
            }
        }

        return $result;
    }

    /**
     * @Route(
     *     "/time-performance",
     *     name="time_performance",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function timePerformanceAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        // Média de tempo de viagem por sentido da faixa horária

        $result = $this->getTimePerformanceResult($formFactory);

        return $this->response(200, [
            'data' => $result,
        ]);
    }

    public function getTimePerformanceResult(TelemetrySearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
        ;

        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.starts_at between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }
        $trips = $list->getQuery()->getResult();

        $result = [];
        $hour = 0;
        while ($hour < 24) {
            $cont = 0;
            $seconds = 0;
            $aux = 0;
            $average = 0;
            $min = 0;
            foreach ($trips as $trip) {
                $start = \DateTime::createFromFormat('Y-m-d H:i:s', $trip->getStartsAt()->format('Y-m-d H:i:s'));
                $end = \DateTime::createFromFormat('Y-m-d H:i:s', $trip->getStartsAt()->format('Y-m-d H:i:s'));
                $start->setTime($hour, 0, 0);
                $end->setTime($hour + 1, 0, 0);
                if ($trip->getStartsAt() >= $start && $trip->getStartsAt() < $end) {
                    $aux = $trip->getStartsAt()->diff($trip->getEndsAt());
                    if (is_null($trip->getEndsAt())) {
                        $now = new \DateTime();
                        $aux = $trip->getStartsAt()->diff($now);
                    }
                    $seconds += ($aux->format('%H') * 3600) + ($aux->format('%I') * 60) + $aux->format('%S');
                    ++$cont;
                }
            }
            if ($cont > 0) {
                $average = $seconds / $cont;
                $min = $average / 60 >= 1 ? intval($average / 60) : 0;
            }

            $result[] = [
                'description' => $hour,
                'average' => $min,
            ];
            ++$hour;
        }

        return $result;
    }

    /**
     *Gráfico-2-Tempo de viagem.
     *
     * @Route(
     *     "/average-consumption",
     *     name="average_consumption",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function averageConsumptionAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        // Fator de cumprimento de viagem

        $result = $this->getAverageConsumptionResult($formFactory);

        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getAverageConsumptionResult(TelemetrySearchTypeFactory $formFactory)
    {
        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $trips
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $trips
                    ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.starts_at between :date' . $key . 'start and :date' . $key . 'end';

                    $trips->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $trips->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $trips->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $trips
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $trips
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $trips
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $trips
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $trips
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }
        $trips = $trips->getQuery()->getResult();

        $result = [];
        $day = 0;
        $auxEnd = 0;
        if (!isset($searchData['sequence']) || count($searchData['sequence']) <= 0) {
            while ($startsAt <= $endsAt) {
                $onTime = 0;
                $late = 0;
                $advance = 0;

                foreach ($trips as $trip) {
                    if (!is_null($trip->getScheduleDate()) && $startsAt->format('d/m') === $trip->getStartsAt()->format('d/m')) {
                        $timeSchedule = $trip->getScheduleDate()->getSchedule()->getStartsAt();
                        if ($trip->getStartsAt()->format('H:i') > (clone $timeSchedule)->modify('+10 minutes')->format('H:i')) {
                            ++$late;
                        } elseif ($trip->getStartsAt()->format('H:i') < (clone $timeSchedule)->modify('-10 minutes')->format('H:i')) {
                            ++$advance;
                        } else {
                            ++$onTime;
                        }
                    }
                }
                $scheduleDates = $this->getEntityManager()
                    ->getRepository(ScheduleDate::class)
                    ->createQueryBuilder('e')
                    ->andWhere('e.date = :startsAt')
                    ->setParameter('startsAt', $startsAt->format('Y-m-d'))
                    ->getQuery()
                    ->getResult()
                ;

                $qtdeSchedules = count($scheduleDates);
                if ($qtdeSchedules > 0) {
                    $result[] = [
                        'name' => $startsAt->format('d/m'),
                        'onTime' => ($onTime / $qtdeSchedules) * 100,
                        'late' => ($late / $qtdeSchedules) * 100,
                        'advance' => ($advance / $qtdeSchedules) * 100,
                    ];
                } else {
                    $result[] = [
                        'name' => $startsAt->format('d/m'),
                        'onTime' => 0,
                        'late' => 0,
                        'advance' => 0,
                    ];
                }

                $startsAt->add(new \DateInterval('P1D'));
            }
        } else {
            $startsAts = $searchData['sequence'];
            foreach ($startsAts as $startsAt) {
                $onTime = 0;
                $late = 0;
                $advance = 0;

                foreach ($trips as $trip) {
                    if (!is_null($trip->getScheduleDate()) && $startsAt->format('d/m') === $trip->getStartsAt()->format('d/m')) {
                        $timeSchedule = $trip->getScheduleDate()->getSchedule()->getStartsAt();
                        if ($trip->getStartsAt()->format('H:i') > (clone $timeSchedule)->modify('+10 minutes')->format('H:i')) {
                            ++$late;
                        } elseif ($trip->getStartsAt()->format('H:i') < (clone $timeSchedule)->modify('-10 minutes')->format('H:i')) {
                            ++$advance;
                        } else {
                            ++$onTime;
                        }
                    }
                }
                $scheduleDates = $this->getEntityManager()
                    ->getRepository(ScheduleDate::class)
                    ->createQueryBuilder('e')
                    ->andWhere('e.date = :startsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->getQuery()
                    ->getResult()
                ;

                $qtdeSchedules = count($scheduleDates);
                if ($qtdeSchedules > 0) {
                    $result[] = [
                        'name' => $startsAt->format('d/m'),
                        'onTime' => ($onTime / $qtdeSchedules) * 100,
                        'late' => ($late / $qtdeSchedules) * 100,
                        'advance' => ($advance / $qtdeSchedules) * 100,
                    ];
                } else {
                    $result[] = [
                        'name' => $startsAt->format('d/m'),
                        'onTime' => 0,
                        'late' => 0,
                        'advance' => 0,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     *Tabela-1-Tempo de viagem.
     *
     * @Route(
     *     "/time-trip-table",
     *     name="time_trip_table",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function timeTripTableAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        $result = $this->getTimeTripTableResult($formFactory);
        if (is_array($result)) {
            return $this->response(200, $result);
        }

        return $result;
    }

    public function getTimeTripTableResult(TelemetrySearchTypeFactory $formFactory)
    {
        $list = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->innerJoin(Schedule::class, 'schedule', 'WITH', 'e.schedule = schedule.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'schedule.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        $form = $formFactory->getFormHandled();

        if (is_null($form->getData())) {
            $form->setData([
                'days' => '7',
            ]);
            $endsAt = new \DateTime();
            $startsAt = (clone $endsAt)->sub(new \DateInterval('P7D'))->setTime(0, 0, 0);
        }

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $list
                    ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $list
                    ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.date between :date' . $key . 'start and :date' . $key . 'end';

                    $list->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $list->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $list->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $list
                    ->andWhere('schedule.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $list
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $list
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $list
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $list
                    ->andWhere('line.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }

        $list = $list->getQuery()->getResult();

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.scheduleDate in (:scheduleDates)')
            ->setParameter('scheduleDates', $list)
            ->addOrderBy('e.id', 'ASC')
        ;

        $result = [];
        if (!isset($searchData['sequence']) || count($searchData['sequence']) <= 0) {
            while ($startsAt <= $endsAt) {
                $qtdeTrip = 0;
                $qtdeSchedule = 0;
                $tripFail = 0;

                foreach ($trips as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtdeTrip;
                    }
                }

                foreach ($list as $scheduleDate) {
                    if ($scheduleDate->getDate()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtdeSchedule;
                    }
                }

                $tripFail = $qtdeSchedule - $qtdeTrip;
                if ($qtdeSchedule > 0) {
                    $result[] = [
                        'day' => $startsAt->format('d/m'),
                        'punctualityIndexPercent' => 100 * ($tripFail / $qtdeSchedule),
                    ];
                } else {
                    $result[] = [
                        'day' => $startsAt->format('d/m'),
                        'punctualityIndexPercent' => 0,
                    ];
                }

                $startsAt->add(new \DateInterval('P1D'));
            }
        } else {
            $startsAts = $searchData['sequence'];
            foreach ($startsAts as $startsAt) {
                $qtdeTrip = 0;
                $qtdeSchedule = 0;
                $tripFail = 0;

                foreach ($trips as $trip) {
                    if ($trip->getStartsAt()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtdeTrip;
                    }
                }

                foreach ($list as $scheduleDate) {
                    if ($scheduleDate->getDate()->format('d/m') == $startsAt->format('d/m')) {
                        ++$qtdeSchedule;
                    }
                }

                $tripFail = $qtdeSchedule - $qtdeTrip;
                if ($qtdeSchedule > 0) {
                    $result[] = [
                        'day' => $startsAt->format('d/m'),
                        'punctualityIndexPercent' => 100 * ($tripFail / $qtdeSchedule),
                    ];
                } else {
                    $result[] = [
                        'day' => $startsAt->format('d/m'),
                        'punctualityIndexPercent' => 0,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @Route(
     *     "/list-drivers-ranking",
     *     name="list-drivers-ranking",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listDriversRankingAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        $driversConsumption = $this->driversRankingAction();

        return $this->response(200, $driversConsumption);
    }

    public function driversRankingAction()
    {
        $now = new \DateTime();
        $startMonth = (clone $now)->setDate($now->format('Y'), $now->format('m'), 1)->setTime(0, 0, 0);
        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.starts_at > :start')
            ->setParameter('start', $startMonth)
            ->orderBy('e.id', 'ASC')
        ;

        $trips = $trips->getQuery()->getResult();

        $drivers = [];
        foreach ($trips as $trip) {
            if (!(in_array($trip->getDriver(), $drivers))) {
                array_push($drivers, $trip->getDriver());
            }
        }

        $consumption = 0;
        $qtdeTrips = 0;
        $driversConsumption = [];
        foreach ($drivers as $driver) {
            foreach ($trips as $trip) {
                if ($driver == $trip->getDriver()) {
                    ++$qtdeTrips;
                    if ($trip->getReport() && $trip->getReport()->getConsumption()) {
                        $consumption += $trip->getReport()->getConsumption();
                    }
                }
            }
            if ($driver) {
                $driversConsumption[$driver->getCode() . ' - ' . $driver->getName()] = $consumption / $qtdeTrips;
            }
            $qtdeTrips = 0;
            $consumption = 0;
        }

        arsort($driversConsumption);

        return $driversConsumption;
    }

    /**
     * @Route(
     *     "/factor-compliance",
     *     name="factor-compliance",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function factorComplianceAction(TelemetrySearchTypeFactory $formFactory): JsonResponse
    {
        $schedules = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->innerJoin(Schedule::class, 'schedule', 'WITH', 'e.schedule = schedule.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'schedule.line = line.id')
            ->orderBy('e.date', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
            $startsAt = null;
            $endsAt = null;

            $now = new \DateTime();
            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] === null) {
                $startsAt = $searchData['start'];
                $endsAt = (clone $now);
            }

            if (isset($searchData['start']) && $searchData['start'] === null && $searchData['end'] != null) {
                $startsAt = (clone $now);
                $endsAt = $searchData['end'];
            }

            if (isset($searchData['start']) && $searchData['start'] != null && $searchData['end'] != null) {
                $startsAt = $searchData['start'];
                $endsAt = $searchData['end'];
            }

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $schedules
                    ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($searchData['days']) && !is_null($searchData['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $searchData['days'] . 'D'))->setTime(0, 0, 0);
                $endsAt = (clone $now);
                $schedules
                    ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $now)
                ;
            }

            if (isset($searchData['sequence']) && count($searchData['sequence']) > 0 && !is_null($searchData['sequence'][0])) {
                $query = '';
                foreach ($searchData['sequence'] as $key => $date) {
                    if ($key > 0) {
                        $query .= ' or ';
                    }
                    $query .= 'e.date between :date' . $key . 'start and :date' . $key . 'end';

                    $schedules->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $schedules->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $schedules->andWhere($query);
            }

            if (isset($searchData['line']) && count($searchData['line']) > 0) {
                $schedules
                    ->andWhere('e.line in (:lines)')
                    ->setParameter('lines', $searchData['line'])
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $schedules
                    ->andWhere('line.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['driver']) && count($searchData['driver']) > 0) {
                $schedules
                    ->andWhere('e.driver in (:drivers)')
                    ->setParameter('drivers', $searchData['driver'])
                ;
            }

            if (isset($searchData['vehicle']) && count($searchData['vehicle']) > 0) {
                $schedules
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $schedules
                    ->andWhere('line.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }

        $schedules = $schedules->getQuery()->getResult();

        $result = [];
        $resultFinal = [];
        foreach ($schedules as $schedule) {
            if (!in_array($schedule->getDate(), $result)) {
                array_push($result, $schedule->getDate());
            }
        }

        $scale = 0;
        foreach ($result as $day) {
            foreach ($schedules as $schedule) {
                if ($day == $schedule->getDate()) {
                    ++$scale;
                }
            }

            $tripEnd = (clone $day)->add(new \DateInterval('P1D'));
            $trips = $this->getEntityManager()
                ->getRepository(Trip::class)
                ->createQueryBuilder('e')
                ->andWhere('e.scheduleDate IN (:schedulesDate) ')
                ->andWhere('e.starts_at > :start ')
                ->andWhere('e.starts_at BETWEEN :start AND :end')
                ->setParameter('start', $day)
                ->setParameter('end', $tripEnd)
                ->setParameter('schedulesDate', $schedule)
                ->getQuery()
                ->getResult()
            ;

            $qttdTrips = count($trips);
            $notPerformed = $scale - $qttdTrips;
            if ($notPerformed) {
                $percent = $notPerformed / $scale;
            } else {
                $percent = 1;
            }
            $percent = $percent * 100;

            array_push($resultFinal, [$day->format('d/m/Y'), $percent]);
            $scale = 0;
        }

        return $this->response(200, $resultFinal);
    }

    public function driverRankingConsumptionAction()
    {
        $now = new \DateTime();
        $startMonth = (clone $now)->setDate($now->format('Y'), $now->format('m'), 1)->setTime(0, 0, 0);
        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e.id) as qtt, employee.id')
            ->leftJoin('e.employee', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.start > :start')
            ->setParameter('start', $startMonth)
            ->groupBy('e.employee')
            ->setParameter('modality', EmployeeModality::DRIVER)
            ->setMaxResults(10)
            ->orderBy('qtt', 'ASC');

        if (is_object($this->getUser()->getCompany())) {
            $qb->andWhere('company.id = :company')->setParameter('company', $company);
        }

        $list = $qb->getQuery()->getResult();

        $employeesId = [];
        foreach ($list as $row) {
            $employeesId[] = $row['id'];
        }

        $qb = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('modality', EmployeeModality::DRIVER)
            ->setParameter('ids', $employeesId)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $employees = $qb->getQuery()->getResult();

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver IN (:drivers)')
            ->andWhere('e.starts_at > :start')
            ->setParameter('start', $startMonth)
            ->setParameter('drivers', $employeesId)
            ->getQuery()
            ->getResult()
        ;

        $preResult = [];
        $result = [];
        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.starts_at > :start')
            ->setParameter('start', $startMonth)
            ->getQuery()
            ->getResult()
        ;

        $qb = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->setParameter('modality', EmployeeModality::DRIVER)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $employees = $qb->getQuery()->getResult();
        $preResult = [];
        foreach ($employees as $key => $employee) {
            $cont = 0;
            $kml = 0;

            foreach ($trips as $trip) {
                if ($trip->getDriver() == $employee && !empty($trip->getReport())) {
                    $kml += $trip->getReport()->getConsumption();
                    ++$cont;
                }
            }
            if ($cont > 0) {
                $teste = strval($kml / $cont);
                $preResult[$teste]['kmL'] = $kml / $cont;
                $preResult[$teste]['employee'] = $employee;
            }
        }
        krsort($preResult);
        foreach ($preResult as $value) {
            array_push($result, $value);
        }

        return $result;
    }

    /**
     * @Route(
     *     "/export-consumption",
     *     name="export_consumption",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function exportConsumptionAction(TelemetrySearchTypeFactory $formFactory)
    {
        $consumptionCsv = fopen('arquivo.csv', 'w');

        fputcsv($consumptionCsv, ['Consumo']);
        fputcsv($consumptionCsv, []);

        fputcsv($consumptionCsv, ['Melhores e piores consumos do período']);
        // api/telemetry/fuel-consumption
        $fuelConsumption = $this->getFuelResult($formFactory);
        if (!is_array($fuelConsumption)) {
            return $fuelConsumption;
        }
        fputcsv($consumptionCsv, ['Dia', 'Melhor', 'Pior', 'Média']);
        if (count($fuelConsumption) > 0) {
            foreach ($fuelConsumption as $consumption) {
                fputcsv($consumptionCsv, $consumption);
            }
        }
        fputcsv($consumptionCsv, []);

        fputcsv($consumptionCsv, ['Relação da média do consumo/hora do período']);
        // api/telemetry/consumption-time
        fputcsv($consumptionCsv, ['Dia', 'Faixa horária', 'Média de consumo do dia', 'Média na faixa horária']);
        $consumptions = $this->getConsumptionTimeResult($formFactory);
        if (!is_array($consumptions)) {
            return $consumptions;
        }
        if (count($consumptions) > 0) {
            foreach ($consumptions as $media) {
                $times = $media['times'];
                foreach ($times as $time) {
                    fputcsv($consumptionCsv, [$media['description'], $time[0], $media['average'], $time[1]]);
                }
            }
        }
        fputcsv($consumptionCsv, []);

        fputcsv($consumptionCsv, ['Motoristas com melhor desempenho - Km/L']);
        // pi/dashboard/driver-ranking/fuel/best
        fputcsv($consumptionCsv, ['Motorista', 'Km/L']);
        $kmLs = $this->driverRankingConsumptionAction();
        if (count($kmLs) > 0) {
            foreach ($kmLs as $kmL) {
                fputcsv($consumptionCsv, [$kmL['employee']->getCode() . ' - ' . $kmL['employee']->getName(), $kmL['kmL']]);
            }
        }
        fputcsv($consumptionCsv, []);

        fputcsv($consumptionCsv, ['Motoristas com melhor desempenho - Km percorrido/Ocorrência']);
        // api/telemetry/list-drivers-ranking
        $drivers = $this->driversRankingAction();
        fputcsv($consumptionCsv, ['Motorista', 'Km percorrido/Ocorrência']);
        if (count($drivers) > 0) {
            foreach ($drivers as $key => $driver) {
                fputcsv($consumptionCsv, [$key, $driver]);
            }
        }
        fputcsv($consumptionCsv, []);

        fclose($consumptionCsv);

        return $this->file('arquivo.csv');
    }

    /**
     * @Route(
     *     "/export-occurrence",
     *     name="export_occurrence",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function exportOccurrenceAction(TelemetryOccurrenceSearchTypeFactory $formFactory)
    {
        $occurrenceCsv = fopen('arquivo.csv', 'w');

        fputcsv($occurrenceCsv, ['Ocorrências']);
        fputcsv($occurrenceCsv, []);

        fputcsv($occurrenceCsv, ['Frequência de ocorrências em todas as viagens do período']);
        // api/telemetry/event-frequency
        $frequencyResult = $this->getFrequencyResult($formFactory);
        if (!is_array($frequencyResult)) {
            return $frequencyResult;
        }
        fputcsv($occurrenceCsv, ['Descrição', 'Quantidade']);
        if (count($frequencyResult) > 0) {
            foreach ($frequencyResult as $frequency) {
                fputcsv($occurrenceCsv, [$frequency['description'], $frequency['qtt']]);
            }
        }
        fputcsv($occurrenceCsv, []);

        fputcsv($occurrenceCsv, ['Total de ocorrência no período']);
        // api/telemetry/total-event-by-date
        $eventDates = $this->getEventDateResult($formFactory);
        if (!is_array($eventDates)) {
            return $eventDates;
        }
        fputcsv($occurrenceCsv, ['Dia', 'Quantidade']);
        if (count($eventDates) > 0) {
            foreach ($eventDates as $event) {
                fputcsv($occurrenceCsv, [$event['description'], $event['qtt']]);
            }
        }
        fputcsv($occurrenceCsv, []);

        fclose($occurrenceCsv);

        return $this->file('arquivo.csv');
    }

    /**
     * @Route(
     *     "/export-time-trip",
     *     name="export_time_trip",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function exportTimeTripAction(TelemetrySearchTypeFactory $formFactory)
    {
        $timeTripCsv = fopen('arquivo.csv', 'w');

        fputcsv($timeTripCsv, ['Tempo de viagem']);
        fputcsv($timeTripCsv, []);

        fputcsv($timeTripCsv, ['Média de tempo de viagem por sentido da faixa horária']);
        // api/telemetry/time-performance (grafico 1)
        $performanceResult = $this->getTimePerformanceResult($formFactory);
        if (!is_array($performanceResult)) {
            return $performanceResult;
        }
        fputcsv($timeTripCsv, ['Hora', 'Quantidade de minutos']);
        if (count($performanceResult) > 0) {
            foreach ($performanceResult as $performance) {
                fputcsv($timeTripCsv, [$performance['description'], $performance['average']]);
            }
        }
        fputcsv($timeTripCsv, []);

        fputcsv($timeTripCsv, ['Fator de cumprimento de viagem']);
        // api/telemetry/average-consumption (grafico 2)
        $averageConsumptions = $this->getAverageConsumptionResult($formFactory);
        if (!is_array($averageConsumptions)) {
            return $averageConsumptions;
        }
        fputcsv($timeTripCsv, ['Dia', 'No horario', 'Atrasado', 'Adiantado']);
        if (count($averageConsumptions) > 0) {
            foreach ($averageConsumptions as $average) {
                fputcsv($timeTripCsv, [$average['name'], $average['onTime'], $average['late'], $average['advance']]);
            }
        }
        fputcsv($timeTripCsv, []);

        fputcsv($timeTripCsv, ['Índice de pontualidade']);
        // api/telemetry/time-trip-table (grafico 3)
        $timeTrips = $this->getTimeTripTableResult($formFactory);
        if (!is_array($timeTrips)) {
            return $timeTrips;
        }
        fputcsv($timeTripCsv, ['Dia', 'Índice de Pontualidade']);
        if (count($timeTrips) > 0) {
            foreach ($timeTrips as $trip) {
                fputcsv($timeTripCsv, [$trip['day'], $trip['punctualityIndexPercent']]);
            }
        }
        fputcsv($timeTripCsv, []);

        fclose($timeTripCsv);

        return $this->file('arquivo.csv');
    }
}
