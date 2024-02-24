<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\CompanyPlace;
use App\Entity\Employee;
use App\Entity\FuelQuote;
use App\Entity\Line;
use App\Entity\Obd;
use App\Entity\Parameter;
use App\Entity\ParameterConfiguration;
use App\Entity\Role;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Form\Api\CompanySearchTypeFactory;
use App\Form\Api\CompanyTypeFactory;
use App\Buslab\Utils\LogCreator;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/company",
 *     name="api_adm_company_"
 * )
 */
class CompanyController extends AbstractApiController
{
    /**
     * API-040.
     *
     * @Route(
     *     "/list",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function listAction(
        CompanySearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.city', 'city')
            ->leftJoin('city.state', 'state')
            ->addOrderBy('e.description', 'ASC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb
                ->andWhere('e.id = :company')
                ->setParameter('company', $company->id);
        }

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (strlen($searchData['descriptionName']) > 0) {
                $qb
                    ->andWhere('e.description LIKE :description')
                    ->setParameter('description', '%' . $searchData['descriptionName'] . '%')
                ;
            }

            if (count($searchData['city']) > 0) {
                $qb
                    ->andWhere('e.city in (:citys)')
                    ->setParameter('citys', $searchData['city'])
                ;
            }

            if (count($searchData['state']) > 0) {
                $qb
                    ->andWhere('model.state in (:states)')
                    ->setParameter('states', $searchData['state'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-041.
     *
     * @Route(
     *     "/new",
     *     name="new",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function newAction(
        CompanyTypeFactory $formFactory,
        Request $request
    ): JsonResponse {
        $entity = new Company();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        $parameters = $this->getEntityManager()
            ->getRepository(Parameter::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $min = 0;
        $max = 0;
        foreach ($parameters as $key => $parameter) {
            if ($parameter->getId() == 1) {// velocidade
                $max = 60;
            }
            if ($parameter->getId() == 2) {// RPM
                $max = 2500;
            }
            if ($parameter->getId() == 3) {// Idade do veículo
                $max = 10;
            }
            $parameterConfiguration = (new ParameterConfiguration())
            ->setParameter($parameter)
            ->setCompany($entity)
            ->setMaxAllowed($max)
            ->setMinAllowed($min)
            ;

            $this->persist($parameterConfiguration);
        }

        return $this->response(200, $entity);
    }

    /**
     * API-042.
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
    public function showAction(Company $entity): JsonResponse
    {        
        if ($entity->getLatitude() === null && $entity->getLongitude() === null) {            
            $address = $entity->getStreetName() . ' ' . $entity->getStreetNumber() . ' ' . $entity->getCity()->getName();


            $url = 'https://maps.google.com/maps/api/geocode/json?key=' . getenv('GOOGLE_MAPS_APIKEY') . '&address=' . urlencode($address);

            $resp_json = file_get_contents($url);
            $resp = json_decode($resp_json, true);

            if ($resp['status'] == 'OK') {
                $entity
                    ->setLatitude($resp['results'][0]['geometry']['location']['lat'])
                    ->setLongitude($resp['results'][0]['geometry']['location']['lng'])
                ;

                $this->persist($entity);
            }
        }

        return $this->response(200, $entity);
    }

    /**
     * API-043.
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
        Company $entity,
        CompanyTypeFactory $formFactory
    ): JsonResponse {
        $prevPlan = $entity->getRolePlan();
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if (
            $form->get('streetName')->getData() ||
            $form->get('streetNumber')->getData() ||
            $form->get('city')->getData()
        ) {
            $address = $entity->getStreetName() . ' ' . $entity->getStreetNumber() . ' ' . $entity->getCity()->getName()
            ;

            $url = 'https://maps.google.com/maps/api/geocode/json?key=' . getenv('GOOGLE_MAPS_APIKEY') . '&address=' . urlencode($address);

            $resp_json = file_get_contents($url);
            $resp = json_decode($resp_json, true);

            if ($resp['status'] == 'OK') {
                $entity
                    ->setLatitude($resp['results'][0]['geometry']['location']['lat'])
                    ->setLongitude($resp['results'][0]['geometry']['location']['lng'])
                ;
            }
        }
        $getNowPlan = $entity->getRolePlan();
        $this->persist($entity);
        if ($prevPlan != $getNowPlan) {
            $users = $this->getEntityManager()
                ->getRepository(User::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = :entity')
                ->setParameter('entity', $entity)
                ->addOrderBy('e.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;

            foreach ($users as $key => $user) {
                $user->setRolePlan($entity->getRolePlan());
                $this->persist($user);
            }
        }

        return $this->response(200, $entity);
    }

    /**
     *  .
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
    public function removeAction(Company $entity): JsonResponse
    {
        $obd = $this->getEntityManager()
            ->getRepository(Obd::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $vehicle = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $employee = $this->getEntityManager()
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $fuel = $this->getEntityManager()
            ->getRepository(FuelQuote::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $line = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $parameter = $this->getEntityManager()
            ->getRepository(ParameterConfiguration::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $trip = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = (:company)')
            ->setParameter('company', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($trip) > 0 || count($parameter) || count($line) || count($fuel) || count($employee) || count($vehicle) || count($obd)) {
            return $this->responseError(400, 'Esta empresa não pode ser removida, pois está associada a um ou mais registros.');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-045.
     *
     * @Route(
     *     "/{company}/obd/{obd}/add",
     *     name="add_obd",
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
    public function addObdAction(Company $company, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCompany($company));

        return $this->emptyResponse();
    }

    /**
     * API-046.
     *
     * @Route(
     *     "/{company}/obd/{obd}/remove",
     *     name="remove_obd",
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
    public function removeObdAction(Company $company, Obd $obd): JsonResponse
    {
        $this->persist($obd->setCompany(null));

        return $this->emptyResponse();
    }

    /**
     * @deprecated
     * @Route(
     *     "/{company}/obd/{obd}",
     *     name="add_obd_deprecated",
     *     format="json",
     *     methods={"POST"},
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
    public function addObdDeprecatedRouteAction(Company $company, Obd $obd): JsonResponse
    {
        return $this->addObdAction($company, $obd);
    }

    /**
     * @deprecated
     * @Route(
     *     "/{company}/obd/{obd}",
     *     name="remove_obd_deprecated",
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
    public function removeObdDeprecatedRouteAction(Company $company, Obd $obd): JsonResponse
    {
        return $this->removeObdAction($company, $obd);
    }

    /**
     * API-097.
     *
     * @Route(
     *     "/{identifier}/vehicle/list",
     *     name="vehicle_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function vehicleListAction(Company $entity): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $entity)
        ;

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/fence",
     *     name="fence",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function fenceAction(Request $request): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(CompanyPlace::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.id', 'ASC')
        ;

        if ($this->getUser()->getCompany()) {
            $qb
            ->andWhere('e.company = :company')
            ->setParameter('company', $this->getUser()->getCompany())
            ;
        }

        return $this->response(200, $this->paginate($qb));
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
    public function removeCascadeAction(Company $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/role-plan/list",
     *     name="role_plan_list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function rolePlanListAction(): JsonResponse
    {
        $qb = $this->getEntityManager()
            ->getRepository(Role::class)
            ->createQueryBuilder('e')
            ->andWhere('e.role IN (:roles)')
            ->setParameter('roles', ['ROLE_BASIC', 'ROLE_STANDARD', 'ROLE_ADVANCED'])
            ->getQuery()
            ->getResult()
        ;

        return $this->response(200, $qb);
    }
}
