<?php

namespace App\Controller\Api\Adm;

use App\Entity\ReportFiles;
use App\Entity\Vehicle;
use App\Form\Api\Adm\ReportFilesSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/report-files",
 *     name="api_adm_report-files_"
 * )
 */
class ReportFilesController extends AbstractApiController
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
        ReportFilesSearchTypeFactory $formFactory
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
            ->getRepository(ReportFiles::class)
            ->createQueryBuilder('e')
            // ->innerJoin(Line::class, 'line', 'WITH', 'schedule.line = line.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        if (!is_null($startsAt) && !is_null($endsAt)) {
            $qb
                ->andWhere('e.createdAt BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $endsAt)
            ;
        }

        if (isset($data['days']) && !is_null($data['days'])) {
            $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
            $qb
                ->andWhere('e.createdAt BETWEEN :startsAt AND :endsAt')
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
                $query .= 'e.createdAt between :date' . $key . 'start and :date' . $key . 'end';

                $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
            }

            $qb->andWhere($query);
        }

        // if (isset($data['line']) && count($data['line']) > 0) {
        //     $qb
        //         ->andWhere('schedule.line = (:line)')
        //         ->setParameter('line', $data['line'])
        //     ;
        // }

        // if (isset($data['vehicle']) && count($data['vehicle']) > 0) {
        //     $qb
        //         ->andWhere('e.vehicle = (:vehicle)')
        //         ->setParameter('vehicle', $data['vehicle'])
        //     ;
        // }

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
        ReportFilesSearchTypeFactory $formFactory
    ): JsonResponse {
        return $this->response(200, '');
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
    public function showAction(ReportFiles $entity): JsonResponse
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
        ReportFiles $entity
    ): JsonResponse {
        return $this->emptyResponse();
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
    public function removeAction(ReportFiles $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }
}
