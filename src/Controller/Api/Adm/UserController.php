<?php

namespace App\Controller\Api\Adm;

use App\Entity\Role;
use App\Entity\User;
use App\Form\Api\UserEditTypeFactory;
use App\Form\Api\UserPasswordTypeFactory;
use App\Form\Api\UserRegisterTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route(
 *     "/api/adm/user",
 *     name="api_adm_user_"
 * )
 */
class UserController extends AbstractApiController
{
    /**
     * API-034.
     *
     * @Route(
     *     "/{role}/list",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json",
     *         "role"="system|company|manager|operator"
     *     }
     * )
     */
    public function listAction(
        string $role,
        ApiSearchFormTypeFactory $formFactory
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(User::class)
            ->createQueryBuilder('e')
            ->where('e.role = :role')
            ->leftJoin('e.company', 'company')
            ->setParameter('role', $this->translateRoleStrToId($role))
            ->addOrderBy('e.name', 'ASC')
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
                        $qb->expr()->like('e.name', ':search'),
                        $qb->expr()->like('e.cellphone', ':search'),
                        $qb->expr()->like('e.email', ':search'),
                        $qb->expr()->like('e.documentNumber', ':search'),
                    ))
                    ->setParameter('search', '%' . $searchData['search'] . '%')
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * API-035.
     *
     * @Route(
     *     "/{role}/new",
     *     name="new",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json",
     *         "role"="system|company|manager|operator"
     *     }
     * )
     */
    public function newAction(
        string $role,
        UserRegisterTypeFactory $formFactory,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request
    ): JsonResponse {
        $em = $this->getDoctrine()->getManager();
        
        $entity = (new User())
            ->setRole($em->getRepository(Role::class)->find($this->translateRoleStrToId($role)))
        ;

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if (!is_null($entity->getCompany())) {
            $entity->setRolePlan($entity->getCompany()->getRolePlan());
        }

        $entity
            ->setPassword($passwordEncoder->encodePassword(
                $entity,
                $entity->getPassword()
            ))
        ;

        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * API-036.
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
    public function showAction(User $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * API-037.
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
        User $entity,
        UserEditTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        if (!$entity->getRole()->isCompanyRelated()) {
            $entity->setCompany(null);
        }

        $this->persist($entity);

        return $this->emptyResponse();
    }

    /**
     * API-038.
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
    public function removeAction(User $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * API-039.
     *
     * @Route(
     *     "/{identifier}/change-password",
     *     name="change_password",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function changePasswordAction(
        User $entity,
        UserPasswordTypeFactory $formFactory,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $entity
            ->setPassword($passwordEncoder->encodePassword(
                $entity,
                $entity->getPassword()
            ))
        ;

        $this->persist($entity);

        return $this->emptyResponse();
    }

    /**
     * Helper function to translate the controller value of role tpo the ID.
     */
    private function translateRoleStrToId(string $role)
    {
        if ('system' === $role) {
            $role = Role::ROLE_SYSTEM_ADMIN;
        } elseif ('company' === $role) {
            $role = Role::ROLE_COMPANY_ADMIN;
        } elseif ('manager' === $role) {
            $role = Role::ROLE_COMPANY_MANAGER;
        } else { // defaults to if ($role === 'operator')
            $role = Role::ROLE_COMPANY_OPERATOR;
        }

        return $role;
    }
}
