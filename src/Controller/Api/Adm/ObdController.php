<?php

namespace App\Controller\Api\Adm;

use App\Entity\CellphoneNumber;
use App\Entity\Checkpoint;
use App\Entity\Company;
use App\Entity\Event;
use App\Entity\Obd;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Form\Api\Adm\ObdSearchTypeFactory;
use App\Form\Api\Adm\ObdTypeFactory;
use App\Repository\CheckpointRepository;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/obd",
 *     name="api_adm_obd_"
 * )
 */
class ObdController extends AbstractApiController
{
    /**
     * API-047.
     *
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
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Obd::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.cellphoneNumber', 'cellphoneNumber')
            ->leftJoin('e.company', 'company')
            ->addOrderBy('e.serial', 'DESC')
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

            if (strlen($searchData['serial']) > 0) {
                $qb
                    ->andWhere('e.serial LIKE :serial')
                    ->setParameter('serial', '%' . $searchData['serial'] . '%')
                ;
            }

            if (strlen($searchData['version']) > 0) {
                $qb
                    ->andWhere('e.version in (:versions)')
                    ->setParameter('versions', $searchData['version'])
                ;
            }

            if (count($searchData['cellphoneNumber']) > 0) {
                $qb
                    ->andWhere('e.cellphoneNumber in (:cellphoneNumbers)')
                    ->setParameter('cellphoneNumbers', $searchData['cellphoneNumber'])
                ;
            }

            if (count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-048.
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
        ObdTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Obd();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $entity->setStatus(false);
        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * API-049.
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
    public function showAction(Obd $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-050.
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
        Obd $entity,
        ObdTypeFactory $formFactory
    ): JsonResponse {
        $em = $this->getEntityManager();

        $cellphone = $entity->getCellphoneNumber();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if ($cellphone != $entity->getCellphoneNumber()) {
            if ($cellphone) {
                $cellphone->setStatus(false);
                $em->persist($cellphone);
            }

            if ($entity->getCellphoneNumber()) {
                $entity->getCellphoneNumber()->setStatus(true);
                $cellphone = $entity->getCellphoneNumber();
                $em->persist($cellphone);
            }
        }

        $em->persist($entity);
        $em->flush();

        return $this->emptyResponse();
    }

    /**
     * API-051.
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
    public function removeAction(Obd $entity): JsonResponse
    {
        $vehicle = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.obd = (:obd)')
            ->setParameter('obd', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $company = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.obd = (:obd)')
            ->setParameter('obd', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($vehicle) > 0 || count($company) > 0) {
            return $this->responseError(400, 'Este obd não pode ser removido, pois possui um ou mais registros associados.');
        }

        $entity->getCellphoneNumber()->setStatus(false);

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-052.
     *
     * @Route(
     *     "/{obd}/company/{company}/add",
     *     name="add_company",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "company"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("company", options={
     *      "mapping"={"company"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function addCompanyAction(Company $company, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCompany($company));

        return $this->emptyResponse();
    }

    /**
     * API-053.
     *
     * @Route(
     *     "/{obd}/company/{company}/remove",
     *     name="remove_company",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "company"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("company", options={
     *      "mapping"={"company"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function removeCompanyAction(Company $company, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCompany(null));

        return $this->emptyResponse();
    }

    /**
     * API-054.
     *
     * @Route(
     *     "/{obd}/cellphone-number/{cellphoneNumber}/add",
     *     name="add_cellphone",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "cellphoneNumber"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("cellphoneNumber", options={
     *      "mapping"={"cellphoneNumber"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function addCellphoneNumberAction(CellphoneNumber $cellphoneNumber, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCellphoneNumber($cellphoneNumber));

        return $this->emptyResponse();
    }

    /**
     * API-055.
     *
     * @Route(
     *     "/{obd}/cellphone-number/{cellphoneNumber}/remove",
     *     name="remove_cellphone",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "cellphoneNumber"="[\w\-\_]{15}",
     *         "obd"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     * @ParamConverter("cellphoneNumber", options={
     *      "mapping"={"cellphoneNumber"="identifier"}
     * })
     * @ParamConverter("obd", options={
     *      "mapping"={"obd"="identifier"}
     * })
     */
    public function removeCellphoneNumberAction(CellphoneNumber $cellphoneNumber, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCellphoneNumber(null));

        return $this->emptyResponse();
    }

    /**
     * API-056.
     *
     * @Route(
     *     "/{obd}/vehicle/{vehicle}/add",
     *     name="add_obd",
     *     format="json",
     *     methods={"POST","PUT"},
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
    public function addVehicleAction(Vehicle $vehicle, Obd $obd): JsonResponse
    {
        $this->persist($obd->setStatus(true));
        $this->persist($vehicle->setObd($obd));

        return $this->emptyResponse();
    }

    /**
     * API-057.
     *
     * @Route(
     *     "/{obd}/vehicle/{vehicle}/remove",
     *     name="remove_obd",
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
    public function removeVehicleAction(Vehicle $vehicle, Obd $obd): JsonResponse
    {
        $this->persist($obd->setStatus(false));
        $this->persist($vehicle->setObd(null));

        return $this->emptyResponse();
    }

    /**
     * API-047.
     *
     * @Route(
     *     "/list-free",
     *     name="list-free",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listFreeAction(): JsonResponse
    {
        $company = $this->getUser()->getCompany();
        $obds = $this->getEntityManager()
            ->getRepository(Obd::class)
            ->createQueryBuilder('e')
        ;

        $vehicles = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
        ;

        if (!is_null($company)) {
            $obds
                ->andWhere('e.company = :company')
                ->setparameter('company', $company)
            ;

            $vehicles
                ->andWhere('e.company = :company')
                ->setparameter('company', $company)
            ;
        }

        $obds = $obds->getQuery()->getResult();

        $vehicles = $vehicles->getQuery()->getResult();

        foreach ($vehicles as $vehicle) {
            if (in_array($vehicle->getObd(), $obds)) {
                $key = array_search($vehicle->getObd(), $obds);
                unset($obds[$key]);
            }
        }

        return $this->response(200, $this->paginate($obds));
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
    public function removeCascadeAction(Obd $entity): JsonResponse
    {
        $number = $entity->getCellphoneNumber();

        $vehicle = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.obd = (:obd)')
            ->setParameter('obd', $entity)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.obd = (:obd)')
            ->setParameter('obd', $entity)
            ->getQuery()
            ->getResult()
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle IN (:vehicle)')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getResult()
        ;

        if ($vehicle) {
            $scheduleDates = $this->getEntityManager()
                ->getRepository(ScheduleDate::class)
                ->createQueryBuilder('e')
                ->andWhere('e.vehicle IN (:vehicle)')
                ->andWhere('e.date between (:initial) and (:final)')
                ->setParameter('vehicle', $vehicle)
                ->setParameter('initial', $trips[0]->getStartsAt())
                ->setParameter('final', end($trips)->getStartsAt())
                ->getQuery()
                ->getResult()
            ;
        }

        $schedules = [];
        foreach ($scheduleDates as $key => $trip) {
            array_push($schedules, $trip->getSchedule());
        }

        $all = [$events, $trips, $scheduleDates, $schedules];
        if (count($trips) > 0 || count($events) > 0 || count($scheduleDates) > 0 || count($schedules) > 0) {
            foreach ($all as $key => $itens) {
                if (!is_null($itens) || count($itens) > 0) {
                    foreach ($itens as $key => $item) {
                        $this->persist($item->setIsActive(false));
                    }
                }
            }
        }

        if (!is_null($number)) {
            $entity->setCellphoneNumber(null);
            $this->persist($number->setStatus(false));
        }

        if (!is_null($vehicle)) {
            $this->persist($vehicle->setObd(null));
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-047.
     *
     * @Route(
     *     "/{identifier}/last-checkpoint",
     *     name="last-checkpoint",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function lastCheckpointAction(Obd $entity, CheckpointRepository $checkpointRepository): JsonResponse
    {
        
        $checkpoint = $checkpointRepository->getLastCheckpoint($entity->id);

        if (is_null($checkpoint)) {                        
            return $this->emptyResponse();
        }

        return $this->response(200, $checkpoint->getDate());
    }
}
