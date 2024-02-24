<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\Event;
use App\Entity\Obd;
use App\Entity\ParameterConfiguration;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Entity\VehicleModel;
use App\Entity\VehicleStatus;
use App\Form\Api\Adm\ImportTypeFactory;
use App\Form\Api\Adm\VehicleSearchTypeFactory;
use App\Form\Api\Adm\VehicleTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateInterval;

/**
 * @Route(
 *     "/api/adm/vehicle",
 *     name="api_adm_vehicle_"
 * )
 */
class VehicleController extends AbstractApiController
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
        VehicleSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.model', 'model')
            ->leftJoin('e.company', 'company')
            ->leftJoin('e.obd', 'obd')
            ->leftJoin('model.brand', 'brand')
            ->leftJoin('e.status', 'status')
            ->addOrderBy('e.prefix', 'ASC')
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

            if (strlen($searchData['search']) > 0) {
                $qb
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->like('e.plate', ':search'),
                        $qb->expr()->like('e.consumptionTarget', ':search'),
                        $qb->expr()->like('e.startOperation', ':search'),
                        $qb->expr()->like('e.manufacture', ':search'),
                        $qb->expr()->like('e.chassi', ':search'),
                        $qb->expr()->like('e.prefix', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }

            if (strlen($searchData['plate']) > 0) {
                $qb
                    ->andWhere('e.plate LIKE :plate')
                    ->setParameter('plate', '%' . $searchData['plate'] . '%')
                ;
            }

            if (count($searchData['obd']) > 0) {
                $qb
                    ->andWhere('e.obd in (:obds)')
                    ->setParameter('obds', $searchData['obd'])
                ;
            }

            if (strlen($searchData['prefix']) > 0) {
                $qb
                    ->andWhere('e.prefix LIKE :prefix')
                    ->setParameter('prefix', '%' . $searchData['prefix'] . '%')
                ;
            }

            if (count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }

            if (count($searchData['model']) > 0) {
                $qb
                    ->andWhere('e.model in (:models)')
                    ->setParameter('models', $searchData['model'])
                ;
            }

            if (count($searchData['brand']) > 0) {
                $qb
                    ->andWhere('model.brand in (:brands)')
                    ->setParameter('brands', $searchData['brand'])
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
        VehicleTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Vehicle();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if ($entity->getObd()) {
            $obd = $entity->getObd();
            $obd->setStatus(true);
            $this->persist($obd);
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
    public function showAction(Vehicle $entity): JsonResponse
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
        Vehicle $entity,
        VehicleTypeFactory $formFactory
    ): JsonResponse {
        $temp = clone $entity;
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if ($temp->getObd() != $entity->getObd()) {
            if (empty($temp->getObd()) && !empty($entity->getObd())) {
                $obd = $entity->getObd();
                $obd->setStatus(true);
                $this->persist($obd);
            }
            if (!empty($temp->getObd()) && empty($entity->getObd())) {
                $obd = $temp->getObd();
                $obd->setStatus(false);
                $this->persist($obd);
            }
            if (!empty($entity->getObd()) && !empty($temp->getObd())) {
                $obd = $temp->getObd();
                $obd->setStatus(false);
                $obd2 = $entity->getObd();
                $obd2->setStatus(true);
                $this->persist($obd);
                $this->persist($obd2);
            }
        }

        $vehicleStatus = $this->getEntityManager()
            ->getRepository(VehicleStatus::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $parameters = $this->getEntityManager()
            ->getRepository(ParameterConfiguration::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $entity->getCompany())
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $periodicTime = 0;
        foreach ($parameters as $key => $parameter) {
            if ($parameter->getParameter()->getDescription() == 'Tempo de validade de inspeção obrigatória') {
                $periodicTime = $parameter->getMaxAllowed();
            }
        }

        $lastInspection = clone $entity->getPeriodicInspection();

        $PeriodicTimeFormated = null;
        if ($periodicTime != 0 && !is_null($periodicTime)) {
            $PeriodicTimeFormated = new DateInterval('P' . $periodicTime . 'M');
        }else{
            $PeriodicTimeFormated = new DateInterval('P6M');
        }

        $now = new \DateTime();
        $shouldInspectDate = $now->sub($PeriodicTimeFormated);

        if ($entity->getStatus()->getId() == 2 && $lastInspection > $shouldInspectDate) {
            $entity->setStatus($vehicleStatus[0]);
        }

        if ($entity->getStatus()->getId() == 1 && $shouldInspectDate > $lastInspection) {
            $entity->setStatus($vehicleStatus[1]);
        }

        //Seta status de Retido ou não de acordo com a data de inspeção
        /* $timeLimit = 0;
        foreach ($parameters as $key => $parameter) {
            if ($parameter->getParameter()->getDescription() == 'Tempo de validade de inspeção obrigatória') {
                $timeLimit = $parameter->getMaxAllowed();
            }
        }

        $limit = clone $entity->getPeriodicInspection();
        $time = new DateInterval('P6M');
        if ($timeLimit != 0 && !is_null($timeLimit)) {
            $time = new DateInterval('P' . $limit . 'M');
        }

        $limit->add($time);
        $now = new \DateTime();

        if ($entity->getStatus()->getId() == 2 && $now < $limit) {
            $entity->setStatus($vehicleStatus[0]);
        }

        if ($entity->getStatus()->getId() == 1 && $now > $limit) {
            $entity->setStatus($vehicleStatus[1]);
        } */

        $this->persist($entity);

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
    public function removeAction(Vehicle $entity): JsonResponse
    {
        $occurrences = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $schedules = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($occurrences) > 0 || count($schedules) > 0) {
            return $this->responseError(400, 'Este veículo não pode ser removido, pois está associado a um ou mais registros vinculados.');
        }

        $obd = $entity->getObd();
        $obd->setStatus(false);
        $this->persist($obd);

        $entity->setObd(null);
        $entity->setIsActive(false);
        $this->persist($entity);

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{vehicle}/obd/{obd}",
     *     name="vinculate_obd",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "vehicle"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("vehicle", options={
     *      "mapping"={"vehicle"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function vinculateObdAction(Vehicle $vehicle, Obd $obd): JsonResponse
    {
        $this->persist($obd->setStatus(true));
        $this->persist($vehicle->setObd($obd));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{vehicle}/obd/{obd}",
     *     name="desvinculate_obd",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "vehicle"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("vehicle", options={
     *      "mapping"={"vehicle"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function desvinculateObdAction(Vehicle $vehicle, Obd $obd): JsonResponse
    {
        $this->persist($vehicle->setObd(null));
        $this->persist($obd->setStatus(false));

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

        if (!($form->getData()['file'])) {
            return $this->responseError(400, 'Sem arquivo de importação.');
        }

        $filePath = $form->getData()['file']->getRealPath();
        $csv = array_map('str_getcsv', file($filePath));

        $vehicles = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $models = $this->getEntityManager()
            ->getRepository(VehicleModel::class)
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

        $obds = $this->getEntityManager()
            ->getRepository(Obd::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $vehicleStatus = $this->getEntityManager()
            ->getRepository(VehicleStatus::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        //Garantir que não puche nenhuma linha inexistente
        $csv = array_filter($csv, function ($value){
            return !empty($value[0]);
        });

        foreach ($csv as $key => $value) {
            $vehicle = new Vehicle();
            $vehicle->setPrefix($value[0]);
            if (strlen($value[1]) != 8) {
                return $this->responseError(400, 'Erro na placa na linha ' . ($key + 1) . '.');
            }
            $vehicle->setPlate($value[1]);

            if ($value[2] == '-') {
                $vehicle->setChassi(null);
            } else {
                if (strlen($value[2]) != 17) {
                    return $this->responseError(400, 'Erro no número do chassi na linha ' . ($key + 1) . '.');
                }
                $vehicle->setChassi($value[2]);
            }

            if (strlen(strval(intval($value[3]))) != 4) {// verifica se caracteres são todos numeros e se a quantidade de anos esta com 4 caracteres
                return $this->responseError(400, 'Erro no ano de fabricação na linha ' . ($key + 1) . '.');
            }
            $vehicle->setManufacture($value[3]);

            foreach ($models as $model) {
                if ($value[4] == $model->getDescription()) {
                    $vehicle->setModel($model);
                    continue;
                }
            }

            if (is_null($vehicle->getModel())) {
                return $this->responseError(400, 'Modelo na linha ' . ($key + 1) . ', não encontrado');
            }

            $vehicle->setBodywork($value[5]);
            if (strlen(strval(intval($value[6]))) != 4) {// verifica se caracteres são todos numeros e se a quantidade de anos esta com 4 caracteres
                return $this->responseError(400, 'Erro no ano de fabricação do chassi na linha ' . ($key + 1) . '.');
            }
            $vehicle->setManufactoreBodywork($value[6]);

            $vehicle->setDoorsNumber($value[7]);
            $vehicle->setConsumptionTarget($value[8]);
            $start = date_create_from_format('d/m/Y', '1/' . $value[9])->setTime(0, 0, 0);
            $vehicle->setStartOperation($start);

            if ($value[10] == '-') {
                $vehicle->setObd(null);
            } else {
                foreach ($obds as $obd) {
                    if ($value[10] == $obd->getSerial()) {
                        foreach ($vehicles as $cars) {
                            if ($cars->getObd() == $obd) {
                                return $this->responseError(400, 'Obd na linha ' . ($key + 1) . ', ja associado a um veículo');
                            }
                        }

                        $vehicle->setObd($obd);
                        break;
                    }
                }
            }

            foreach ($companys as $company) {
                if ($value[11] == $company->getDescription()) {
                    $vehicle->setCompany($company);
                    continue;
                }
            }

            if (is_null($vehicle->getCompany())) {
                return $this->responseError(400, 'Empresa na linha ' . ($key + 1) . ',não encontrada');
            }

            $inspection = date_create_from_format('d/m/Y', $value[14])->setTime(0, 0, 0);
            $vehicle
            ->setSeats($value[12])
            ->setStanding($value[13])
            ->setPeriodicInspection($inspection)
            ;

            $status = strtoupper(trim($value[15]));
            if ($status == 'LIBERADO') {
                $vehicle->setStatus($vehicleStatus[0]);
            }
            if ($status == 'RETIDO') {
                $vehicle->setStatus($vehicleStatus[1]);
            }

            $this->persist($vehicle->getObd()->setStatus(true));
            $this->persist($vehicle);
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
    public function removeCascadeAction(Vehicle $entity): JsonResponse
    {
        $obd = $entity->getObd();

        $scheduleDates = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity)
            ->getQuery()
            ->getResult()
        ;

        $schedule = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity)
            ->getQuery()
            ->getResult()
        ;

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity)
            ->getQuery()
            ->getResult()
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle = (:vehicle)')
            ->setParameter('vehicle', $entity)
            ->getQuery()
            ->getResult()
        ;

        $all = [$events, $trips, $scheduleDates, $schedule];
        if (count($trips) > 0 || count($events) > 0 || count($scheduleDates) > 0 || count($schedule) > 0) {
            foreach ($all as $key => $itens) {
                if (!is_null($itens) || count($itens) > 0) {
                    foreach ($itens as $key => $item) {
                        $this->persist($item->setIsActive(false));
                    }
                }
            }
        }

        if (!is_null($obd)) {
            $entity->setObd(null);
            $this->persist($obd->setStatus(false));
        }
        $entity->setIsActive(false);
        $this->persist($entity);

        return $this->emptyResponse();
    }
}
