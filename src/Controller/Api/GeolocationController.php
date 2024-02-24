<?php
//teste
namespace App\Controller\Api;

use App\Entity\Checkpoint;
use App\Entity\City;
use App\Entity\State;
use App\Entity\Vehicle;
use App\Entity\Schedule;
use App\Form\Api\GeolocationSearchTypeFactory;
use App\Form\Api\VehicleLocationSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use maxh\Nominatim\Nominatim;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/geolocation",
 *     name="api_geolocation_"
 * )
 */
class GeolocationController extends AbstractApiController
{
    /**
     * API-011.
     *
     * @Route(
     *     "/search",
     *     name="search",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function searchAction(GeolocationSearchTypeFactory $formFactory): JsonResponse
    {
        $form = $formFactory->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $data = $form->getData();

        $nominatim = new Nominatim('http://nominatim.openstreetmap.org/');

        $search = $nominatim->newSearch()->country('Brazil')->addressDetails();

        if ($data['city']) {
            $search
                ->city($data['city']->getName())
                ->state($data['city']->getState()->getName())
            ;
        } elseif ($data['state']) {
            $search->state($data['state']->getName());
        }

        if ($data['search']) {
            $search->query($data['search']);
        }

        $result = $nominatim->find($search);

        return $this->response(200, $result);
    }

    /**
     * API-076.
     *
     * @Route(
     *     "/search-batch",
     *     name="search_batch",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function searchBatchAction(GeolocationSearchTypeFactory $formFactory): JsonResponse
    {
        $form = $formFactory->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $data = $form->getData();

        $nominatim = new Nominatim('http://nominatim.openstreetmap.org/');

        $search = $nominatim->newSearch()->country('Brazil')->addressDetails();

        if ($data['city']) {
            $search
                ->city($data['city']->getName())
                ->state($data['city']->getState()->getName())
            ;
        } elseif ($data['state']) {
            $search->state($data['state']->getName());
        }

        $result = [];

        $addresses = explode("\n", $data['search']);

        foreach ($addresses as $key => $address) {
            $address = trim($address);
            if (empty($address)) {
                continue;
            }

            $search->query($address);

            $current = null;
            foreach ($nominatim->find($search) as $data) {
                if (empty($current) || $current['importance'] < $data['importance']) {
                    $current = $data;
                }
            }

            if (is_array($current)) {
                $result[$key] = [
                    'lat' => round(floatval($current['lat']), 7),
                    'lon' => round(floatval($current['lon']), 7),
                    'address' => $current['address'],
                ];
            } else {
                $result[$key] = 'Não encontrado: ' . $address;
            }
        }

        return $this->response(200, $result);
    }

    /**
     * API-012.
     *
     * @Route(
     *     "/state/list",
     *     name="state_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listStateAction(
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(State::class)
            ->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.code', ':search'),
                        $qb->expr()->like('e.initials', ':search'),
                        $qb->expr()->like('e.name', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-013.
     *
     * @Route(
     *     "/state/{identifier}/show",
     *     name="state_show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showStateAction(State $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-014.
     *
     * @Route(
     *     "/state/{identifier}/cities",
     *     name="state_cities",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function listStateCitiesAction(
        State $entity,
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(City::class)
            ->createQueryBuilder('e')
            ->andWhere('e.state = :state')
            ->setParameter('state', $entity)
            ->orderBy('e.name', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.code', ':search'),
                        $qb->expr()->like('e.name', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $qb->getQuery()->getResult());
    }

    /**
     * API-015.
     *
     * @Route(
     *     "/city/list",
     *     name="city_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listCityAction(
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(City::class)
            ->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
        ;

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.code', ':search'),
                        $qb->expr()->like('e.name', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-016.
     *
     * @Route(
     *     "/city/{identifier}/show",
     *     name="city_show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showCityAction(City $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/vehicle-location",
     *     name="vehicle_location",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function vehicleLocation(
        VehicleLocationSearchTypeFactory $formFactory
    ): JsonResponse {
        $bus = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
        ;

        $form = $formFactory->getFormHandled();
        $data = $form->getData();
        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $bus
                ->andWhere('e.company = :company')
                ->setParameter('company', $company);
        }

        if (isset($data['company']) && count($data['company']) > 0) {
            $bus
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $data['company'])
            ;
        }
        $bus = $bus->getQuery()->getResult();

        $result = null;

        foreach ($bus as $key => $vehicle) {
            $checkpoints = $this->getEntityManager()
                ->getRepository(Checkpoint::class)
                ->createQueryBuilder('e')
                ->andWhere('e.vehicle = :vehicle')
                ->setParameter('vehicle', $vehicle)
                ->orderBy('e.id', 'DESC')
                ->setMaxResults(1)
                ;

            if ((isset($data['line']) && count($data['line']) > 0) || 
                (isset($data['driver']) && count($data['driver']) > 0)) {
                    $checkpoints
                        ->innerJoin(Schedule::class, 'schedule', 'WITH', 'schedule.vehicle = (:vehicle)')
                        ->setParameter('vehicle', $vehicle)
                    ;
                }

            if (isset($data['line']) && count($data['line']) > 0) {
                $checkpoints                               
                    ->andWhere('schedule.line = :line')
                    ->setParameter('line', $data['line'])                    
                ;
               /*  $checkpoints
                    ->andWhere('schedule.line = (:line)')
                    ->setParameter('line', $data['line'])
                ; */
            }

            if (isset($data['driver']) && count($data['driver']) > 0) {
                $checkpoints 
                    ->andWhere('schedule.driver = :driver')
                    ->setParameter('driver', $data['driver']) 
                ;
                /* $checkpoints
                    ->andWhere('e.driver = (:employee)')
                    ->setParameter('employee', $data['driver'])
                ; */
            }

            if (isset($data['vehicle']) && count($data['vehicle']) > 0) {
                $checkpoints
                    ->andWhere('e.vehicle = (:vehicle)')
                    ->setParameter('vehicle', $data['vehicle'])
                ;
            }

            $query = $checkpoints->getQuery()->getSQL();
            $checkpoints = $checkpoints->getQuery()->getResult();

            if (count($checkpoints) > 0) {
                $checkpoint = $checkpoints[0];

                $resultPreliminar = [
                    'now' => $checkpoint->getDate(),
                    'date' => $checkpoint->getDate(),
                    'prefix' => $checkpoint->getVehicle()->getPrefix(),
                    'identifier' => $checkpoint->getVehicle()->getIdentifier(),
                    'company' => $checkpoint->getVehicle()->getCompany()->getIdentifier(),
                    'color' => $checkpoint->getVehicle()->getCompany()->getColor(),
                    'driver' => $checkpoint->getTrip() ? $checkpoint->getTrip()->getDriver() : null,
                    'driverIdentifier' => $checkpoint->getTrip() ? $checkpoint->getTrip()->getDriver()->getIdentifier() : null,
                    'line' => $checkpoint->getTrip() ? $checkpoint->getTrip()->getLine()->getDescription() : null,
                    'lineIdentifier' => $checkpoint->getTrip() ? $checkpoint->getTrip()->getLine()->getIdentifier() : null,
                    'latitude' => $checkpoint->getLatitude(),
                    'longitude' => $checkpoint->getLongitude(),
                    'speed' => $checkpoint->getSpeed(),
                    'rpm' => $checkpoint->getRpm(),
                    'status' => null,
                    'lastPoint' => null,
                    'tripIdentifier' => $checkpoint->getTrip() ? $checkpoint->getTrip()->getidentifier() : null,
                    'temperatura' => $checkpoint->getEct(),
                    'address' => null,
                    ];

                $result[$checkpoint->getVehicle()->getIdentifier()] = $resultPreliminar;
            }
        }

        if(!isset($result)){
            return $this->response(200, '');    
        }

        return $this->response(200, $result);
    }
}
