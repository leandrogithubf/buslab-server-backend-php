<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserValidation;
use App\Form\Api\ApplyForgotCodeTypeFactory;
use App\Form\Api\RecoverTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route(
 *     "/api/recover",
 *     name="api_recover_"
 * )
 */
class RecoverController extends AbstractApiController
{
    /**
     * API-002.
     *
     * @Route(
     *     "/request",
     *     name="request",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function requestAction(
        Request $request,
        RecoverTypeFactory $formFactory
    ): JsonResponse {
        $em = $this->getDoctrine()->getManager();

        $form = $formFactory
            ->addCustomFields()
            ->getFormHandled()
        ;

        if (!$form->isSubmitted()) {
            return $this->get('tn.utils.api.response')
                ->error(400, 'app.page_errors.generic_error')
            ;
        }

        if (!$form->isValid()) {
            return $this->get('tn.utils.api.response')
                ->error(400, 'app.page_errors.form.invalid')
            ;
        }

        $isCaptchaValid = $this->get('service_container')
            ->get('tn.security.recaptcha')
            ->validate(
                $form->get('recaptcha_token')->getData()
            )
        ;

        if (!$isCaptchaValid) {
            return $this->get('tn.utils.api.response')->error(400, 'app.page_erros.recaptcha_invalid.400');
        }

        $user = $this->getDoctrine()->getManager()
            ->getRepository(User::class)
            ->findOneByEmail($form->getData()['user'])
        ;

        if (is_object($user)) {
            $translator = $this->get('service_container')->get('translator');

            $pastRecovery = $em->getRepository(UserValidation::class)->findByUser($user);

            foreach ($pastRecovery as $singlePastRecovery) {
                $singlePastRecovery->setIsUsed(true);
                $em->persist($singlePastRecovery);
            }

            $recovery = (new UserValidation())
                ->setUser($user)
                ->setIsUsed(false)
            ;

            $em->persist($recovery);
            $em->flush();

            $this->get('service_container')->get('tn.mailer')
                ->setTo($user->getEmail())
                ->setSubject($translator->trans('app.security.recover.email.subject'))
                ->renderView('recover/recover.html.twig', [
                    'user' => $user,
                    'recovery' => $recovery,
                ])
                ->send()
            ;
        }

        return $this
            ->get('tn.utils.api.response')
            ->emptyResponse()
        ;
    }

    /**
     * API-003.
     *
     * @Route(
     *     "/send",
     *     name="send",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function sendAction(
        Request $request,
        ApplyForgotCodeTypeFactory $formFactory,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $em = $this->getDoctrine()->getManager();

        $form = $formFactory
            ->addCustomFields()
            ->getFormHandled()
        ;

        if (!$form->isSubmitted()) {
            return $this->get('tn.utils.api.response')
                ->error(400, 'app.page_errors.email.not_send')
            ;
        }

        if (!$form->isValid()) {
            return $this->get('tn.utils.api.response')
                ->errorFromForm($form->getErrors(true))
            ;
        }

        $formData = $form->getData();

        $isCaptchaValid = $this->get('service_container')
            ->get('tn.security.recaptcha')
            ->validate(
                $form->get('recaptcha_token')->getData()
            )
        ;

        if (!$isCaptchaValid) {
            return $this->get('tn.utils.api.response')->error(400, 'app.page_erros.recaptcha_invalid.400');
        }

        $code = $formData['code'];
        $password = $formData['password'];

        $forgotCode = $em->getRepository(UserValidation::class)->findOneValidByCode($code);

        if (!$forgotCode) {
            return $this->get('tn.utils.api.response')
                ->error(400, 'Código inválido')
            ;
        }

        $user = $forgotCode->getUser();
        $user->setPassword($passwordEncoder->encodePassword($user, $password));

        $forgotCode->setIsUsed(true);

        $em->flush();

        return $this
            ->get('tn.utils.api.response')
            ->emptyResponse()
        ;
    }
}
