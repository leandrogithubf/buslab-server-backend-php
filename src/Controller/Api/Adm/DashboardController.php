<?php

namespace App\Controller\Api\Adm;

use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\EventModality;
use App\Entity\Schedule;
use App\Entity\Trip;
use App\Form\Api\Adm\EventSearchTypeFactory;
use App\Form\Api\Adm\OccurrenceSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/dashboard",
 *     name="api_adm_dashboard_"
 * )
 */
class DashboardController extends AbstractApiController
{
    /**
     * @Route(
     *     "/list-occurrence",
     *     name="list-occurrence",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listOccurrenceAction(
        OccurrenceSearchTypeFactory $formFactory
    ): JsonResponse {
        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 1')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $now = new \DateTime();
        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->andWhere('e.start > (:start)')
            ->setParameter('modality', $modality)
            ->orderBy('e.id', 'DESC')
            ->setParameter('start', $now->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getResult()
        ;

        $categorys = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
        }

        $occurrences = [];
        foreach ($categorys as $category) {
            $i = 0;
            foreach ($qb as $event) {
                if ($event->getCategory() == $category) {
                    ++$i;
                }
            }
            $occurrences[] = [$category->getDescription(), $i];
        }

        return $this->response(200, $occurrences);
    }

    /**
     * @Route(
     *     "/list-event",
     *     name="list-event",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listEventAction(
        EventSearchTypeFactory $formFactory
    ): JsonResponse {
        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 2')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $now = new \DateTime();
        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->setParameter('modality', $modality)
            ->andWhere('e.start > (:start)')
            ->setParameter('start', $now->sub(new \DateInterval('P30D')))
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $categorys = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();
        }

        $events = [];
        foreach ($categorys as $category) {
            $i = 0;
            foreach ($qb as $event) {
                if ($event->getCategory() == $category) {
                    ++$i;
                }
            }
            $events[] = [$category->getDescription(), $i];
        }

        return $this->response(200, $events);
    }

    /**
     * @Route(
     *     "/list-drivers",
     *     name="list-drivers",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listDriversAction(): JsonResponse
    {
        $drivers = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this->response(200, $this->paginate($drivers));
    }

    /**
     * @Route(
     *     "/list-trip",
     *     name="list-trip",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listTripAction(): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.schedule IS NOT NULL')
        ;

        $schedule = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
        ;

        $now = new \DateTime();
        $today = $now;
        $yesterday = (clone $now)->sub(new \DateInterval('P2D'));
        $week = (clone $now)->sub(new \DateInterval('P5D'));
        $month = (clone $now)->sub(new \DateInterval('P24D'));
        $qbThirtyDays = $qb
                    ->andWhere('e.starts_at between (:start) and (:today)')
                    ->setParameter('start', $month)
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult()
                    ;

        $scheduleThirtyDays = $schedule
                            ->andWhere('e.data_validity between (:start) and (:today)')
                            ->setParameter('start', $month)
                            ->setParameter('today', $today)
                            ->getQuery()
                            ->getResult()
                            ;
        $programadas = count($scheduleThirtyDays);
        $pontuais = 0;
        $atrasadas = 0;
        $adiantadas = 0;
        $naoRealizadas = count($scheduleThirtyDays) - count($qbThirtyDays);
        foreach ($qbThirtyDays as $trip) {
            if (strtotime($trip->getEndsAt()->add(new \DateInterval('PT5M'))->format('H:i:s')) > strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$atrasadas;
            } elseif (strtotime($trip->getEndsAt()->sub(new \DateInterval('PT5M'))->format('H:i:s')) < strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$adiantadas;
            } else {
                ++$pontuais;
            }
        }

        $statusTripThirtyDays = [
            'programadas' => $programadas,
            'atrasadas' => $atrasadas,
            'adiantadas' => $adiantadas,
            'pontuais' => $pontuais,
            'naoRealizadas' => $naoRealizadas,
        ];

        $qbSevenDays = $qb
                    ->andWhere('e.starts_at between (:start) and (:today)')
                    ->setParameter('start', $week)
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult()
                    ;

        $scheduleSevenDays = $schedule
                            ->andWhere('e.data_validity between (:start) and (:today)')
                            ->setParameter('start', $week)
                            ->setParameter('today', $today)
                            ->getQuery()
                            ->getResult()
                            ;

        $programadas = count($scheduleSevenDays);
        $pontuais = 0;
        $atrasadas = 0;
        $adiantadas = 0;
        $naoRealizadas = count($scheduleSevenDays) - count($qbSevenDays);
        foreach ($qbSevenDays as $trip) {
            if (strtotime($trip->getEndsAt()->add(new \DateInterval('PT5M'))->format('H:i:s')) > strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$atrasadas;
            } elseif (strtotime($trip->getEndsAt()->sub(new \DateInterval('PT5M'))->format('H:i:s')) < strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$adiantadas;
            } else {
                ++$pontuais;
            }
        }

        $statusTripSevenDays = [
            'programadas' => $programadas,
            'atrasadas' => $atrasadas,
            'adiantadas' => $adiantadas,
            'pontuais' => $pontuais,
            'naoRealizadas' => $naoRealizadas,
        ];

        $qbYesterdayDays = $qb
                    ->andWhere('e.starts_at between (:start) and (:today)')
                    ->setParameter('start', $yesterday)
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult()
                    ;

        $scheduleYesterdayDays = $schedule
                            ->andWhere('e.data_validity between (:start) and (:today)')
                            ->setParameter('start', $yesterday)
                            ->setParameter('today', $today)
                            ->getQuery()
                            ->getResult()
                            ;

        $programadas = count($scheduleYesterdayDays);
        $pontuais = 0;
        $atrasadas = 0;
        $adiantadas = 0;
        $naoRealizadas = count($scheduleYesterdayDays) - count($qbYesterdayDays);
        foreach ($qbYesterdayDays as $trip) {
            if (strtotime($trip->getEndsAt()->add(new \DateInterval('PT5M'))->format('H:i:s')) > strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$atrasadas;
            } elseif (strtotime($trip->getEndsAt()->sub(new \DateInterval('PT5M'))->format('H:i:s')) < strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$adiantadas;
            } else {
                ++$pontuais;
            }
        }

        $statusTripYesterdayDays = [
            'programadas' => $programadas,
            'atrasadas' => $atrasadas,
            'adiantadas' => $adiantadas,
            'pontuais' => $pontuais,
            'naoRealizadas' => $naoRealizadas,
        ];

        $qbTodayDays = $qb
                    ->andWhere('e.starts_at > (:start)')
                    ->setParameter('start', $today)
                    ->getQuery()
                    ->getResult()
                    ;

        $scheduleTodayDays = $schedule
                            ->andWhere('e.data_validity > (:start)')
                            ->setParameter('start', $today)
                            ->getQuery()
                            ->getResult()
                            ;

        $programadas = count($scheduleTodayDays);
        $pontuais = 0;
        $atrasadas = 0;
        $adiantadas = 0;
        $naoRealizadas = count($scheduleTodayDays) - count($qbTodayDays);
        foreach ($qbTodayDays as $trip) {
            if (strtotime($trip->getEndsAt()->add(new \DateInterval('PT5M'))->format('H:i:s')) > strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$atrasadas;
            } elseif (strtotime($trip->getEndsAt()->sub(new \DateInterval('PT5M'))->format('H:i:s')) < strtotime($trip->getSchedule()->getHourTermGoing()->format('H:i:s'))) {
                ++$adiantadas;
            } else {
                ++$pontuais;
            }
        }

        $statusTripTodayDays = [
            'programadas' => $programadas,
            'atrasadas' => $atrasadas,
            'adiantadas' => $adiantadas,
            'pontuais' => $pontuais,
            'naoRealizadas' => $naoRealizadas,
        ];

        $trips = [
            'today' => $statusTripTodayDays,
            'yesterday' => $statusTripYesterdayDays,
            'sevenDays' => $statusTripSevenDays,
            'thirtyDays' => $statusTripThirtyDays,
        ];

        return $this->response(200, $trips);
    }

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
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;

        if (is_object($this->getUser()->getCompany())) {
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $result = $qb->getQuery()->getResult();

        return $this->response(200, $trips);
    }
}
