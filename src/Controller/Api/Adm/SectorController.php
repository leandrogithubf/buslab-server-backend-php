<?php

namespace App\Controller\Api\Adm;

use App\Entity\Sector;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/api/adm/sector",
 *     name="api_adm_sector"
 * )
 */
class SectorController extends AbstractApiController
{
    /**
     * @Route(
     *     "/list/{type}",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *         "type"="ocurrence|event"
     *     }
     * )
     */
    public function listAction(
        ApiSearchFormTypeFactory $formFactory,
        string $type = 'ocurrence'
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Sector::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.id', 'ASC')
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
                        $qb->expr()->like('e.description', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }
}
