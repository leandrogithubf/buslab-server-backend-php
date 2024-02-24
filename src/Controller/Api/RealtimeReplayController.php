<?php

namespace App\Controller\Api;

use App\Entity\Checkpoint;
use App\Entity\Schedule;
use App\Buslab\Utils\LogCreator;
use App\Form\Api\RealtimeSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/realtime-replay",
 *     name="api_realtime_"
 * )
 */
class RealtimeReplayController extends AbstractApiController
{
    /**
     * API-074.
     *
     * @Route(
     *     "/dates",
     *     name="checkpoint_dates",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function ceckpointDatesAction()
    {
        $min = $this->getEntityManager()
            ->getRepository(Checkpoint::class)
            ->createQueryBuilder('e')
            ->select('MIN(e.date)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $max = $this->getEntityManager()
            ->getRepository(Checkpoint::class)
            ->createQueryBuilder('e')
            ->select('MAX(e.date)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $this->response(200, [
            'min' => $min,
            'max' => $max,
        ]);
    }

    /**
     * API-075.
     *
     * @Route(
     *     "/geolocation",
     *     name="geolocation",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function geolocationAction(
        RealtimeSearchTypeFactory $formFactory
    ) {
        $now = new \DateTime();
        $form = $formFactory->setData([
            'startsAt' => $now,
        ])->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }
        }

        $data = $form->getData();
        if ($data['startsAt'] === null) {
            $data['startsAt'] = $now->modify('-30 minutes');
        }

        if (isset($data['endsAt']) && $data['endsAt'] !== null) {
            if ($data['startsAt'] > $data['endsAt']) {
                $aux = $data['endsAt'];
                $data['endsAt'] = $data['startsAt'];
                $data['startsAt'] = $aux;
            }
        }

        //Define a quantidade de tempo máximo de duas horas de diferença
        /* if (
            !isset($data['endsAt'])
            || ($data['endsAt']->getTimestamp() - $data['startsAt']->getTimestamp()) > 7200
        ) {
            $data['endsAt'] = (clone $data['startsAt'])->modify('+2 hour');
        } */

        $qb = $this->getEntityManager()
            ->getRepository(Checkpoint::class)
            ->createQueryBuilder('e')
            ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
            ->setParameter('startsAt', $data['startsAt'])
            ->setParameter('endsAt', $data['endsAt'])
            ->orderBy('e.date', 'ASC')
        ;        

        if (count($data['vehicle']) > 0) {
            $qb
                ->andWhere('e.vehicle = :vehicle')
                ->setParameter('vehicle', $data['vehicle'])
            ;
        }        

        if (count($data['company']) > 0) {
            $qb
                ->join('e.vehicle', 'vehicle')
                ->andWhere('vehicle.company = :company')
                ->setParameter('company', $data['company'])
            ;
        } else {
            if (is_object($this->getUser()->getCompany())) {
                $company = $this->getUser()->getCompany();
                $qb
                    ->join('e.vehicle', 'vehicle')
                    ->andWhere('vehicle.company = :company')
                    ->setParameter('company', $company)
                ;
            }
        }        

        if ((isset($data['line']) && count($data['line']) > 0) || (isset($data['employee']) && count($data['employee']) > 0)) {
            $qb
                ->innerJoin(Schedule::class, 'schedule', 'WITH', 'schedule.vehicle = e.vehicle')
            ;            
        }

        if (isset($data['employee']) && count($data['employee']) > 0) {
            $qb
                ->andWhere('schedule.driver = :employee')
                ->setParameter('employee', $data['employee'])
            ;
        }

        if (isset($data['line']) && count($data['line']) > 0) {
            $qb
                ->andWhere('schedule.line = :line')
                ->setParameter('line', $data['line'])
            ;
        }

        $result = [];
        $firstDate = clone $data['startsAt'];
        $lastDate = clone $data['endsAt'];
        $speedy[] = [];        

        foreach ($qb->getQuery()->getResult() as $row) {
            $identifier = $row->getDate()->format('Y-m-d H:i');
            if (!array_key_exists($identifier, $result)) {
                $result[$identifier] = [];
            }

            $firstDate = min($firstDate, $row->getDate());
            $lastDate = max($lastDate, $row->getDate());

            $speedy = [
                'value' => $row->getSpeed(),
                'unit' => 'Km/h',
            ];

            $result[$identifier][] = [
                'date' => $row->getDate(),
                'company' => $row->getVehicle()->getCompany(),
                'color' => $row->getVehicle()->getCompany()->getColor(),
                'vehicle' => $row->getVehicle()->getPrefix(),
                'driver' => $row->getTrip() ? $row->getTrip()->getDriver()->getName() : '-',
                'driverIdentifier' => $row->getTrip() ? $row->getTrip()->getDriver()->getIdentifier() : '-',
                'lastPoint' => '-',
                'line' => $row->getTrip() ? $row->getTrip()->getLine()->getLabel() : '-',
                'lineIdentifier' => $row->getTrip() ? $row->getTrip()->getLine()->getIdentifier() : '-',
                'latitude' => $row->getLatitude(),
                'longitude' => $row->getLongitude(),
                'speed' => is_null($speedy) ? '-' : $speedy,
                'rpm' => is_null($row->getRpm()) ? '-' : $row->getRpm(),
                'temperatura' => is_null($row->getEct()) ? '-' : $row->getEct(),
                'tripIdentifier' => is_null($row->getTrip()) ? '-' : $row->getTrip()->getIdentifier(),
                'status' => is_null($row->getTrip()) ? 'OFF_ROUTE' : 'IN_ROUTE',
            ];
        }

        if ($lastDate < $firstDate) {
            if ($lastDate === null) {
                return $this->responseError(400, 'Não existem a partir dessa data');
            }

            $lastDate = $lastDate['date'];
            $firstDate = $lastDate;
        }

        $diff = $firstDate->diff($lastDate, true);

        return $this->response(200, [
            'firstDate' => $firstDate,
            'lastDate' => $lastDate,
            'secondsBetweenDates' => ($diff->format('%h') * 60 * 60) + ($diff->format('%i') * 60) + $diff->format('%s'),
            'nextStartsAt' => (clone $lastDate)->modify('+1 second'),
            'data' => $result,
        ]);
    }
}
