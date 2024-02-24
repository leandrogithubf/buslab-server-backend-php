<?php

namespace App\Controller\Api;

use App\Entity\Line;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/timeline",
 *     name="api_timeline_"
 * )
 */
class TimelineController extends AbstractApiController
{
    /**
     * API-.
     *
     * @Route(
     *     "/build",
     *     name="build",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function buildAction()
    {
        $lines = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.company', 'company')
            ->orderBy('e.code', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this->response(200, [
            'min' => $min,
            'max' => $max,
        ]);
    }
}
