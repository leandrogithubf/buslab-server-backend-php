<?php

namespace App\Controller\Api\Adm;

use App\Entity\Company;
use App\Entity\Event;
use App\Entity\Line;
use App\Entity\LinePoint;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Form\Api\Adm\ImportTypeFactory;
use App\Form\Api\Adm\LinePointsTypeFactory;
use App\Form\Api\Adm\LineSearchTypeFactory;
use App\Form\Api\Adm\LineTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/line",
 *     name="api_adm_line"
 * )
 */
class LineController extends AbstractApiController
{
    /**
     * API-083.
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
        LineSearchTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.company', 'company')
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

            if (isset($searchData['code']) && strlen($searchData['code']) > 0) {
                $qb
                    ->andWhere('e.code LIKE :code')
                    ->setParameter('code', '%' . $searchData['code'] . '%')
                ;
            }

            if (isset($searchData['description']) && strlen($searchData['description']) > 0) {
                $qb
                    ->andWhere('e.description LIKE :description')
                    ->setParameter('description', '%' . $searchData['description'] . '%')
                ;
            }

            if (isset($searchData['direction']) && strlen($searchData['direction']) > 0) {
                $qb
                    ->andWhere('e.direction LIKE :direction')
                    ->setParameter('direction', '%' . $searchData['direction'] . '%')
                ;
            }

            if (isset($searchData['company']) && count($searchData['company']) > 0) {
                $qb
                    ->andWhere('e.company in (:companys)')
                    ->setParameter('companys', $searchData['company'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-084.
     *
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
        LineTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Line();

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $em = $this->getEntityManager();

        foreach ($entity->getPoints() as $point) {
            $em->persist($point->setLine($entity));
        }

        $em->persist($entity);
        $em->flush();

        return $this->response(200, $entity);
    }

    /**
     * API-085.
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
    public function showAction(Line $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-086.
     *
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
        Line $entity,
        LineTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $em = $this->getEntityManager();

        foreach ($entity->getPoints() as $point) {
            if (empty($point->getLatitude()) && empty($point->getLongitude())) {
                $em->remove($point);
            } else {
                $em->persist($point->setLine($entity));
            }
        }

        $em->persist($entity);
        $em->flush();

        return $this->emptyResponse();
    }

    /**
     * API-087.
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
    public function removeAction(Line $entity): JsonResponse
    {
        $schedule = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $event = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        $trip = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($schedule) > 0 || count($event) > 0 || count($trip) > 0) {
            return $this->responseError(400, 'Esta linha não pode ser removida, pois está associada a um ou mais registros');
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-088.
     *
     * @Route(
     *     "/{identifier}/points",
     *     name="list_points",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function listPointsAction(Line $entity): JsonResponse
    {
        return $this->response(200, $entity->getPoints());
    }

    /**
     * API-089.
     *
     * @Route(
     *     "/{identifier}/points",
     *     name="edit_points",
     *     format="json",
     *     methods={"POST", "PUT"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function editPointsAction(
        Line $entity,
        LinePointsTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $em = $this->getEntityManager();

        foreach ($entity->getPoints() as $point) {
            if (empty($point->getLatitude()) && empty($point->getLongitude())) {
                $em->remove($point);
            } else {
                $em->persist($point->setLine($entity));
            }
        }

        $em->flush();

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
        $filePath = $form->getData()['file']->getRealPath();
        $csv = array_map('str_getcsv', file($filePath));

        $companys = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        foreach ($csv as $value) {
            $line = new Line();

            $line->setCode($value[0]);
            $line->setDescription($value[1]);
            $line->setPassage($value[3]);
            $line->setMaxSpeed($value[4]);

            foreach ($companys as $company) {
                if ($value[5] == $company->getDescription()) {
                    $line->setCompany($company);
                    continue;
                }
            }

            if (is_null($line->getCompany())) {
                return $this->responseError(400, 'Empresa não encontrado');
            }

            if ($value[2] === 'Ida' || strtoupper($value[2]) == 'IDA') {
                $line->setDirection('GOING');
            }

            if ($value[2] === 'Volta' || strtoupper($value[2]) == 'VOLTA') {
                $line->setDirection('RETURN');
            }

            if ($value[2] === 'Circular' || strtoupper($value[2]) == 'CIRCULAR') {
                $line->setDirection('CIRCULATE');
            }

            if (is_null($line->getDirection())) {
                return $this->responseError(400, 'Sentido não identificado');
            }

            $this->persist($line);
        }

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/export",
     *     name="export",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function exportAction(Line $line)
    {
        $lineCsv = fopen('arquivo.csv', 'w');

        fputcsv($lineCsv, ['Linha', 'Código', 'Sentido', 'Passagem', 'Limite de velocidade', 'Empresa']);
        fputcsv($lineCsv, [$line->getDescription(), $line->getCode(), $line->getDirection() == 'GOING' ? 'Ida' : $line->getDirection() == 'RETURN' ? 'Volta' : 'Circular', $line->getPassage(), $line->getMaxSpeed(), $line->getCompany()->getDescription()]);
        fputcsv($lineCsv, []);

        $points = $this->getEntityManager()
            ->getRepository(LinePoint::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $line->getId())
            ->addOrderBy('e.sequence', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        fputcsv($lineCsv, ['Ordem', 'Endereço', 'Latitude', 'Longitude']);

        foreach ($points as $point) {
            fputcsv($lineCsv, [$point->getSequence(), $point->getAddress(), $point->getLatitude(), $point->getLongitude()]);
        }

        fclose($lineCsv);

        return $this->file('arquivo.csv');
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
    public function removeCascadeAction(Line $entity): JsonResponse
    {
        $linePoints = $this->getEntityManager()
            ->getRepository(LinePoint::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:lines)')
            ->setParameter('lines', $entity)
            ->getQuery()
            ->getResult()
        ;

        $trips = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity)
            ->getQuery()
            ->getResult()
        ;

        $schedules = $this->getEntityManager()
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity)
            ->getQuery()
            ->getResult()
        ;

        $scheduleDates = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->andWhere('e.schedule IN (:schedules)')
            ->setParameter('schedules', $schedules)
            ->getQuery()
            ->getResult()
        ;

        $events = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line = (:line)')
            ->setParameter('line', $entity)
            ->getQuery()
            ->getResult()
        ;

        $all = [$events, $trips, $scheduleDates, $schedules, $linePoints];
        if (count($trips) > 0 || count($events) > 0 || count($scheduleDates) > 0 || count($schedules) > 0 || count($linePoints) > 0) {
            foreach ($all as $key => $itens) {
                if (!is_null($itens) || count($itens) > 0) {
                    foreach ($itens as $key => $item) {
                        $this->persist($item->setIsActive(false));
                    }
                }
            }
        }

        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
