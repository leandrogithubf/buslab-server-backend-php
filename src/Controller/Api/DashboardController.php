<?php

namespace App\Controller\Api;

use App\Entity\Consumption;
use App\Entity\Employee;
use App\Entity\EmployeeModality;
use App\Entity\Event;
use App\Entity\EventModality;
use App\Entity\FuelQuote;
use App\Entity\Schedule;
use App\Entity\Trip;
use App\Entity\TripModality;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/dashboard",
 *     name="api_dashboard_"
 * )
 */
class DashboardController extends AbstractApiController
{
    /**
     * @Route(
     *     "/fuel-quote",
     *     name="fuel_quote",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function fuelQuoteAction(): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(FuelQuote::class)
            ->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->setMaxResults(5)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $list = $qb->getQuery()->getResult();

        $result = [];
        foreach ($list as $row) {
            $date = $row->getDate()->format('Ymd');

            $value = StringUtils::onlyNumbers($row->getValue());

            if (!isset($result[$date])) {
                $result[$date] = [
                    'label' => $row->getDate()->format('d/m'),
                    'date' => $row->getDate(),
                    'average' => $value,
                    'min' => $value,
                    'max' => $value,
                    'qtt' => 1,
                ];
            } else {
                $result[$date]['average'] += $value;
                ++$result[$date]['qtt'];
                $result[$date]['min'] = min($result[$date]['min'], $value);
                $result[$date]['max'] = max($result[$date]['max'], $value);
            }
        }

        foreach ($result as $date => $row) {
            $result[$date]['average'] = $row['average'] / $row['qtt'];
            $result[$date]['average'] = $result[$date]['average'] / 100;
            $result[$date]['min'] = $result[$date]['min'] / 100;
            $result[$date]['max'] = $result[$date]['max'] / 100;
        }

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/consumption",
     *     name="consumption",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function consumptionAction(): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(Consumption::class)
            ->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->setMaxResults(5)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $list = $qb->getQuery()->getResult();

        $result = [];
        foreach ($list as $row) {
            $date = $row->getDate()->format('Ymd');

            $value = StringUtils::onlyNumbers($row->getConsumption());

            if (!isset($result[$date])) {
                $result[$date] = [
                    'label' => $row->getDate()->format('d/m'),
                    'date' => $row->getDate(),
                    'average' => $value,
                    'min' => $value,
                    'max' => $value,
                    'qtt' => 1,
                ];
            } else {
                $result[$date]['average'] += $value;
                ++$result[$date]['qtt'];
                $result[$date]['min'] = min($result[$date]['min'], $value);
                $result[$date]['max'] = max($result[$date]['max'], $value);
            }
        }

        foreach ($result as $date => $row) {
            $result[$date]['average'] = $row['average'] / $row['qtt'];
            $result[$date]['average'] = $result[$date]['average'] / 10;
            $result[$date]['min'] = $result[$date]['min'] / 10;
            $result[$date]['max'] = $result[$date]['max'] / 10;
        }

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/trips",
     *     name="trips",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function tripsAction(): JsonResponse
    {
        $today = (new \DateTime())->setTime(0, 0, 0);
        $month = (clone $today)->setDate(
            $today->format('Y'),
            $today->format('m'),
            1
        );

        $tomorrow = (clone $today)->modify('+1 day');
        $week = $month;
        $yesterday = $month;

        if (intval($today->format('d')) > 7) {
            $week = (clone $today)->sub(new \DateInterval('P7D'));
        }

        if (intval($today->format('d')) > 1) {
            $yesterday = (clone $today)->sub(new \DateInterval('P1D'));
        }
        $qttTripMonth = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.starts_at > :month')
            ->setParameter('modality', TripModality::SCHEDULED)
            ->setParameter('month', $month)
        ;

        $qttTripWeek = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.starts_at > :month')
            ->setParameter('modality', TripModality::SCHEDULED)
            ->setParameter('month', $week)
        ;

        $qttTripYesterday = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.starts_at BETWEEN :yesterday and :today')
            ->setParameter('modality', TripModality::SCHEDULED)
            ->setParameter('yesterday', $yesterday)
            ->setParameter('today', $today)
        ;

        $qttTripToday = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.starts_at > :today')
            ->setParameter('modality', TripModality::SCHEDULED)
            ->setParameter('today', $today)
        ;

        $qttScheduleMonth = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.dataValidity >= :month and e.dataValidity < :tomorrow')
            ->setParameter('month', $month)
            ->setParameter('tomorrow', $tomorrow)
        ;

        $qttScheduleWeek = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.dataValidity >= :week and e.dataValidity < :tomorrow')
            ->setParameter('week', $week)
            ->setParameter('tomorrow', $tomorrow)
        ;

        $qttScheduleYesterday = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.dataValidity >= :yesterday')
            ->andWhere('e.dataValidity < :today')
            ->setParameter('today', $today)
            ->setParameter('yesterday', $yesterday)
        ;

        $qttScheduleToday = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.dataValidity >= :today and e.dataValidity < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'e.vehicle = vehicle.id')
            ->andWhere('e.start > :month')
            ->setParameter('month', $month)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();

            $qttTripMonth->andWhere('e.company = :company')->setParameter('company', $company);
            $qttTripWeek->andWhere('e.company = :company')->setParameter('company', $company);
            $qttTripYesterday->andWhere('e.company = :company')->setParameter('company', $company);
            $qttTripToday->andWhere('e.company = :company')->setParameter('company', $company);
            $qttScheduleMonth->andWhere('e.company = :company')->setParameter('company', $company);
            $qttScheduleWeek->andWhere('e.company = :company')->setParameter('company', $company);
            $qttScheduleYesterday->andWhere('e.company = :company')->setParameter('company', $company);
            $qttScheduleToday->andWhere('e.company = :company')->setParameter('company', $company);
            $events->andWhere('vehicle.company = :company')->setParameter('company', $company);
        }

        $qttTripMonth = $qttTripMonth->getQuery()->getResult();
        $qttTripWeek = $qttTripWeek->getQuery()->getResult();
        $qttTripYesterday = $qttTripYesterday->getQuery()->getResult();
        $qttTripToday = $qttTripToday->getQuery()->getResult();
        $qttScheduleMonth = $qttScheduleMonth->getQuery()->getResult();
        $qttScheduleWeek = $qttScheduleWeek->getQuery()->getResult();
        $qttScheduleYesterday = $qttScheduleYesterday->getQuery()->getResult();
        $qttScheduleToday = $qttScheduleToday->getQuery()->getResult();
        $events = $events->getQuery()->getResult();

        $monthDONE_LATE = 0;
        $monthDONE_EARLY = 0;
        foreach ($qttTripMonth as $trip) {
            foreach ($events as $event) {
                if ($event->getId() == 5 && $event->getTrip() == $trip) {
                    ++$monthDONE_LATE;
                }
                if ($event->getId() == 16 && $event->getTrip() == $trip) {
                    ++$monthDONE_EARLY;
                }
            }
        }

        $weekDONE_LATE = 0;
        $weekDONE_EARLY = 0;
        foreach ($qttTripWeek as $trip) {
            foreach ($events as $event) {
                if ($event->getId() == 5 && $event->getTrip() == $trip) {
                    ++$weekDONE_LATE;
                }
                if ($event->getId() == 16 && $event->getTrip() == $trip) {
                    ++$weekDONE_EARLY;
                }
            }
        }

        $yesterdayDONE_LATE = 0;
        $yesterdayDONE_EARLY = 0;
        foreach ($qttTripYesterday as $trip) {
            foreach ($events as $event) {
                if ($event->getId() == 5 && $event->getTrip() == $trip) {
                    ++$yesterdayDONE_LATE;
                }
                if ($event->getId() == 16 && $event->getTrip() == $trip) {
                    ++$yesterdayDONE_EARLY;
                }
            }
        }

        $todayDONE_LATE = 0;
        $todayDONE_EARLY = 0;
        foreach ($qttTripToday as $trip) {
            foreach ($events as $event) {
                if ($event->getId() == 5 && $event->getTrip() == $trip) {
                    ++$todayDONE_LATE;
                }
                if ($event->getId() == 16 && $event->getTrip() == $trip) {
                    ++$todayDONE_EARLY;
                }
            }
        }

        $now = new \DateTime();
        $result = [
            'total' => [
                'today' => count($qttTripToday),
                'yesterday' => count($qttTripYesterday),
                'week' => count($qttTripWeek),
                'month' => count($qttTripMonth),
            ],
            'today' => [
                'de' => $today,
                'ate' => $now,
                'DONE' => count($qttTripToday) - ($todayDONE_LATE + $todayDONE_EARLY),
                'DONE_LATE' => $todayDONE_LATE,
                'DONE_EARLY' => $todayDONE_EARLY,
                'NOT_DONE' => count($qttScheduleToday) - count($qttTripToday),
            ],
            'yesterday' => [
                'de' => $yesterday,
                'ate' => $now,
                'DONE' => count($qttTripYesterday) - ($yesterdayDONE_LATE + $yesterdayDONE_EARLY),
                'DONE_LATE' => $yesterdayDONE_LATE,
                'DONE_EARLY' => $yesterdayDONE_EARLY,
                'NOT_DONE' => count($qttScheduleYesterday) - count($qttTripYesterday),
            ],
            'week' => [
                'de' => $week,
                'ate' => $now,
                'DONE' => count($qttTripWeek) - ($weekDONE_LATE + $weekDONE_EARLY),
                'DONE_LATE' => $weekDONE_LATE,
                'DONE_EARLY' => $weekDONE_EARLY,
                'NOT_DONE' => count($qttScheduleWeek) - count($qttTripWeek),
            ],
            'month' => [
                'de' => $week,
                'ate' => $now,
                'DONE' => count($qttTripMonth) - ($monthDONE_LATE + $monthDONE_EARLY),
                'DONE_LATE' => $monthDONE_LATE,
                'DONE_EARLY' => $monthDONE_EARLY,
                'NOT_DONE' => count($qttScheduleMonth) - count($qttTripMonth),
            ],
        ];

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/driver-ranking/{modality}/{type}",
     *     name="driver_ranking",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "type"="best|worst",
     *         "modality"="event|fuel",
     *         "_format"="json"
     *     }
     * )
     */
    public function driverRankingAction(
        string $modality = 'event',
        string $type = 'best'
    ): JsonResponse {
        $now = new \DateTime();
        $startMonth = (clone $now)->setDate($now->format('Y'), $now->format('m'), 1)->setTime(0, 0, 0);

        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e.id) as qtt, employee.id')
            ->innerJoin(Employee::class, 'employee', 'WITH', 'e.driver = employee.id')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'e.vehicle = vehicle.id')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.start > :start')
            ->setParameter('start', $startMonth)
            ->groupBy('e.driver')
            ->setParameter('modality', EventModality::EVENT)
            ->setMaxResults(10)
        ;

        if ($type === 'best') {
            $qb->orderBy('qtt', 'ASC');
        } else {
            $qb->orderBy('qtt', 'DESC');
        }

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('vehicle.company = :company')->setParameter('company', $company);
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

        $employees = $qb->getQuery()->getResult();

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.driver IN (:drivers)')
            ->andWhere('e.starts_at > :start')
            ->setParameter('start', $startMonth)
            ->setParameter('drivers', $employeesId)
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $trips->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $trips = $trips->getQuery()->getResult();

        $preResult = [];
        $result = [];
        if ($modality === 'event') {
            foreach ($list as $key => $row) {
                if (empty($row['id'])) {
                    unset($list[$key]);
                    continue;
                }
                foreach ($employees as $employee) {
                    if ($employee->getId() === $row['id'] && !empty($row)) {
                        unset($list[$key]['id']);

                        $kms = 0;
                        foreach ($trips as $trip) {
                            if ($trip->getDriver() == $employee && $trip->getReport()) {
                                $kms += $trip->getReport()->getDistance();
                            }
                        }
                        if ((intval($list[$key]['qtt'])) > 0) {
                            $preResult[$key]['kmOccurremce'] = $kms / intval($list[$key]['qtt']);
                        }
                        $preResult[$key]['kmOccurremce'] = $kms;
                        $preResult[$key]['employee'] = $employee;
                        break;
                    }
                }
            }
            if ($type === 'best') {
                krsort($preResult);
                foreach ($preResult as $value) {
                    array_push($result, $value);
                }
            } else {
                ksort($preResult);
                foreach ($preResult as $value) {
                    array_push($result, $value);
                }
            }
        } else {
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
            if ($type === 'best') {
                krsort($preResult);
                foreach ($preResult as $value) {
                    array_push($result, $value);
                }
            } else {
                ksort($preResult);
                foreach ($preResult as $value) {
                    array_push($result, $value);
                }
            }
        }

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/events",
     *     name="events",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function eventsAction(): JsonResponse
    {
        $now = (new \DateTime());

        $today = [
            'startsAt' => (clone $now)->setTime(0, 0, 0),
            'endsAt' => (clone $now)->setTime(23, 59, 59),
        ];

        $yesterday = [
            'startsAt' => intval($now->format('d')) > 1 ? (clone $today['startsAt'])->modify('-1 day') : (clone $today['startsAt'])->setDate($today['startsAt']->format('Y'), $today['startsAt']->format('m'), 1),
            'endsAt' => (clone $today['endsAt'])->modify('-1 day'),
        ];
        $week = [
            'startsAt' => intval($now->format('d')) > 7 ? (clone $today['startsAt'])->modify('-6 days') : (clone $today['startsAt'])->setDate($today['startsAt']->format('Y'), $today['startsAt']->format('m'), 1),
            'endsAt' => (clone $today['endsAt']),
        ];
        $month = [
            'startsAt' => (clone $today['startsAt'])->setDate(
                $today['startsAt']->format('Y'),
                $today['startsAt']->format('m'),
                1
            ),
            'endsAt' => (clone $today['endsAt'])->setDate(
                $today['startsAt']->format('Y'),
                $today['startsAt']->format('m'),
                $today['startsAt']->format('t'),
            ),
        ];

        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'e.vehicle = vehicle.id')
            ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
            ->setParameter('startsAt', $month['startsAt'])
            ->setParameter('endsAt', $month['endsAt'])
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('vehicle.company = :company')->setParameter('company', $company);
        }

        $list = $qb->getQuery()->getResult();
        $result = [
            'today' => [],
            'yesterday' => [],
            'week' => [],
            'month' => [],
        ];

        $categories = [];
        $idOperacao = 1;
        $idOcorrencia = 1;
        foreach ($list as $row) {
            if ($row->getCategory()->getSector()->getId() != $idOperacao || $row->getModality()->getId() != $idOcorrencia) {
                continue;
            }

            $category = $row->getCategory();
            $categories[$category->getIdentifier()] = $category;

            foreach ($result as $period => $data) {
                if (!isset($result[$period][$category->getIdentifier()])) {
                    $result[$period][$category->getIdentifier()] = [
                        'qtt' => 0,
                        'category' => $category,
                    ];
                }

                if ($$period['startsAt'] <= $row->getStart() && $$period['endsAt'] >= $row->getStart()) {
                    ++$result[$period][$category->getIdentifier()]['qtt'];
                }
            }
        }

        $formatted = [];
        foreach ($categories as $identifier => $category) {
            $formatted[] = [
                'name' => $category->getDescription(),
                'today' => $result['today'][$identifier]['qtt'],
                'yesterday' => $result['yesterday'][$identifier]['qtt'],
                'thisWeek' => $result['week'][$identifier]['qtt'],
                'thisMonth' => $result['month'][$identifier]['qtt'],
            ];
        }

        return $this->response(200, [
            'categories' => $categories,
            'result' => $result,
            'formatted' => $formatted,
        ]);
    }
}
