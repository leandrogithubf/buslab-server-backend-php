<?php

namespace App\Controller\Api;

use App\Entity\EventCategory;
use App\Entity\Line;
use App\Entity\Role;
use App\Form\Api\ProfileLineUpdateTypeFactory;
use App\Form\Api\ProfileNotificationUpdateTypeFactory;
use App\Form\Api\ProfileTypeFactory;
use App\Form\Api\UserPasswordTypeFactory;
use App\Buslab\Controllers\AbstractApiController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route(
 *     "/api/profile",
 *     name="api_profile_"
 * )
 */
class ProfileController extends AbstractApiController
{
    /**
     * API-004.
     *
     * @Route(
     *     "/show",
     *     name="show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function showAction(): JsonResponse
    {
        return $this->response(200, $this->getUser());
    }

    /**
     * API-005.
     *
     * @Route(
     *     "/edit",
     *     name="edit",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function editAction(
        Request $request,
        ProfileTypeFactory $formFactory,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository
    ): JsonResponse {
        $entity = $this->getUser();
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $password = $formFactory->getPassword();
        if($password){
            $entity
            ->setPassword($passwordEncoder->encodePassword($entity,$entity->getPassword()));
        }else{
            $entity->password = $userRepository->getPassword($entity->getUsername());
        }

        $this->persist($entity);

        return $this->emptyResponse();
    }

    /**
     * API-006.
     *
     * @Route(
     *     "/change-password",
     *     name="change_password",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function changePasswordAction(
        UserPasswordTypeFactory $formFactory,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $user = $this->getUser();

        $form = $formFactory->setData($user)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $user
            ->setPassword($passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ))
        ;

        $this->persist($user);

        return $this->emptyResponse();
    }

    /**
     * API-007.
     *
     * @Route(
     *     "/lines/list",
     *     name="lines_list ",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function linesListAction(): JsonResponse
    {
        $user = $this->getUser();

        if ($user->getRole()->getId() !== Role::ROLE_COMPANY_OPERATOR) {
            return $this->response(200, []);
        }

        $list = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $user->getCompany())
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($list as $entity) {
            $result[$entity->getIdentifier()] = [
                'identifier' => $entity->getIdentifier(),
                'code' => $entity->getCode(),
                'description' => $entity->getDescription(),
                'isAttached' => false,
            ];
        }

        $attached = $user->getProfileLines();
        foreach ($attached as $entity) {
            $result[$entity->getIdentifier()]['isAttached'] = true;
        }

        return $this->response(200, $result);
    }

    /**
     * API-008.
     *
     * @Route(
     *     "/lines/update",
     *     name="lines_update ",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function linesUpdateAction(ProfileLineUpdateTypeFactory $formFactory): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getRole()->getId() !== Role::ROLE_COMPANY_OPERATOR) {
            return $this->emptyResponse();
        }

        $form = $formFactory->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $data = $form->getData();

        $list = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->andWhere('e.identifier IN (:identifiers)')
            ->andWhere('e.company = :company')
            ->setParameter('company', $user->getCompany())
            ->setParameter('identifiers', $data['lines'])
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($list as $entity) {
            $result[$entity->getIdentifier()] = $entity;
        }

        $attached = $user->getProfileLines();
        foreach ($attached as $entity) {
            if (!array_key_exists($entity->getIdentifier(), $result)) {
                $user->removeProfileLine($entity);
            } else {
                unset($result[$entity->getIdentifier()]);
            }
        }

        foreach ($result as $entity) {
            $user->addProfileLine($entity);
        }

        $this->persist($user);

        return $this->emptyResponse();
    }

    /**
     * API-009.
     *
     * @Route(
     *     "/notifications/list",
     *     name="notifications_list ",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function notificationsListAction(): JsonResponse
    {
        $user = $this->getUser();

        // if ($user->getRole()->getId() !== Role::ROLE_COMPANY_OPERATOR) {
        //     return $this->response(200, []);
        // }

        $list = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            // ->andWhere('e.company = :company')
            // ->setParameter('company', $user->getCompany())
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($list as $entity) {
            $result[$entity->getIdentifier()] = [
                'identifier' => $entity->getIdentifier(),
                'description' => $entity->getDescription(),
                'isAttached' => false,
            ];
        }

        $attached = $user->getProfileEventCategories();
        foreach ($attached as $entity) {
            $result[$entity->getIdentifier()]['isAttached'] = true;
        }

        return $this->response(200, [
            'isEnabled' => $user->getIsNotificationEnabled(),
            'eventCategories' => $result,
        ]);
    }

    /**
     * API-010.
     *
     * @Route(
     *     "/notifications/update",
     *     name="notifications_update ",
     *     format="json",
     *     methods={"POST","PUT"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function notificationsUpdateAction(ProfileNotificationUpdateTypeFactory $formFactory): JsonResponse
    {
        $user = $this->getUser();

        $form = $formFactory->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $data = $form->getData();

        $list = $this->getEntityManager()
            ->getRepository(EventCategory::class)
            ->createQueryBuilder('e')
            ->andWhere('e.identifier IN (:identifiers)')
            ->setParameter('identifiers', $data['eventCategory'])
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($list as $entity) {
            $result[$entity->getIdentifier()] = $entity;
        }

        $attached = $user->getProfileEventCategories();
        foreach ($attached as $entity) {
            if (!array_key_exists($entity->getIdentifier(), $result)) {
                $user->removeProfileEventCategory($entity);
            } else {
                unset($result[$entity->getIdentifier()]);
            }
        }

        foreach ($result as $entity) {
            $user->addProfileEventCategory($entity);
        }

        $user->setIsNotificationEnabled($data['isEnabled']);

        $this->persist($user);

        return $this->emptyResponse();
    }
}
