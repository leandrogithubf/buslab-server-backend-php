<?php

namespace App\Topnode\AuthBundle\Controller;

use App\Entity\LoginAttempts;
use App\Entity\Role;
use App\Entity\User;
use App\Topnode\AuthBundle\Entity\UserValidation;
use App\Topnode\AuthBundle\Form\LoginType;
use App\Topnode\AuthBundle\Form\RecoverCodeType;
use App\Topnode\AuthBundle\Form\RecoverType;
use App\Topnode\AuthBundle\Form\RedefinePasswordType;
use App\Topnode\AuthBundle\Utils\Generator\AuthRedirectionDecisorGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @Route("/", name="security_")
 */
class SecurityController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'service_container' => \Symfony\Component\DependencyInjection\ContainerInterface::class,
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        AuthRedirectionDecisorGenerator $authRedirector
    ): Response {
        if (is_object($this->getUser())) {
            return $authRedirector->redirect($this->getUser());
        }

        $errorMessage = $authenticationUtils->getLastAuthenticationError();
        if ($errorMessage instanceof \AuthenticationException) {
            $errorMessage = $errorMessage->getMessage();
        }

        if (0 !== strlen($errorMessage)) {
            $this->addFlash('error', $errorMessage);
        }

        $form = $this->createForm(LoginType::class, [
            'user' => $authenticationUtils->getLastUsername(),
        ]);

        $qb = $this->getDoctrine()->getManager()
            ->getRepository(LoginAttempts::class)
            ->createQueryBuilder('e')
        ;

        $ip = $request->getClientIp();

        $attempts = $qb
            ->select('count(e)')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('e.username', ':username'),
                $qb->expr()->eq('e.ip', ':ip')
            ))
            ->andWhere('e.createdAt > :tolerance')
            ->setParameter('tolerance', new \DateTime('10 minutes ago'))
            ->setParameter('username', $authenticationUtils->getLastUsername())
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $this->render('@TopnodeAuth/login.html.twig', [
            'show_captcha' => ($attempts > 2 ? true : false),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     *
     * @throws \Exception
     */
    public function logout(): void
    {
        throw new \Exception('You must activate the logout route on security.yaml');
    }

    /**
     * @Route("/recover", name="recover")
     */
    public function recover(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $form = $this->createForm(RecoverType::class, [
            'user' => $authenticationUtils->getLastUsername() ?? $request->get('user'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isCaptchaValid = $this->get('service_container')
                ->get('tn.security.recaptcha')
                ->validate(
                    $request->request->get('g-recaptcha-response')
                )
            ;

            if ($isCaptchaValid && $form->isValid()) {
                $user = $this->getDoctrine()->getManager()
                    ->getRepository(User::class)
                    ->findOneByEmail($form->getData()['user'])
                ;

                if (is_object($user)) {
                    $translator = $this->get('service_container')->get('translator');
                    $this->get('service_container')->get('tn.mailer')
                        ->setTo($user->getEmail())
                        ->setSubject($translator->trans('app.security.recover.email.subject'))
                        ->renderView('@TopnodeAuth/email/recover.html.twig', [
                            'user' => $user,
                            'recovery' => $this->createRecoverForUser($user),
                        ])
                        ->send()
                    ;
                }

                // Show success message wheter the user has been found or not
                $this->addFlash('success', 'app.security.recover.form.success');

                return $this->redirectToRoute('security_recover_execute');
            }

            $this->addFlash('error', 'app.security.recover.form.error');
        }

        return $this->render('@TopnodeAuth/recover.html.twig', [
            'show_captcha' => true,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/recover/execute", name="recover_execute")
     */
    public function recoverExecute(Request $request): Response
    {
        $identifier = $request->query->get('identifier', null);

        $form = $this->createForm(RecoverCodeType::class, [
            'code' => $identifier,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $identifier = $form->getData()['code'];

                if ($this->codeValidate($identifier)) {
                    return $this->redirectToRoute('security_recover_change', [
                        'identifier' => $identifier,
                    ]);
                }

                $this->addFlash(
                    'error',
                    'app.security.recover_execute.form.link_error'
                );
            } else {
                $this->addFlash('error', 'app.security.recover_execute.form.error');
            }
        }

        return $this->render('@TopnodeAuth/recoverExecute.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * TODO: Send alert by e-mail that the password has been changed on this
     * account for the user.
     *
     * @Route(
     *     "/recover/change/{identifier}",
     *     requirements={
     *         "identifier": "[a-zA-Z0-9-_]{15}"
     *     },
     *     name="recover_change"
     * )
     */
    public function recoverChange(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        AuthRedirectionDecisorGenerator $authRedirector,
        string $identifier
    ): Response {
        $em = $this->getDoctrine()->getManager();

        if (!$this->codeValidate($identifier)) {
            return $this->redirectToRoute('security_recover_execute', [
                'identifier' => $identifier,
            ]);
        }

        $recovery = $em->getRepository(UserValidation::class)->findOneByIdentifier($identifier);
        $user = $recovery->getUser();

        $form = $this->createForm(RedefinePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->setRecoveryStatusFlag($identifier, $isUsed = true);

                $user->setPassword($encoder->encodePassword($user, $form->getData()['password']));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'app.security.recover_change.form.success');

                $this->authenticateUser($request, $user);

                return $authRedirector->redirect($user);
            }

            $this->addFlash('error', 'app.security.recover_change.form.error');
        }

        return $this->render('@TopnodeAuth/recoverChange.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/switch", name="switch")
     */
    public function switch(
        AuthorizationCheckerInterface $authChecker,
        AuthRedirectionDecisorGenerator $authRedirector
    ): Response {
        if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            try {
                return $authRedirector->redirect($this->getUser());
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(Role::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id != :sudoer')
            ->setParameter('sudoer', Role::ROLE_SUPER_ADMIN)
            ->getQuery()
            ->getResult()
        ;

        $qb = $em->getRepository(User::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.name', 'ASC')
        ;

        $usuarios = [];
        foreach ($roles as $role) {
            $usuarios[$role->getId()] = (clone $qb)
                ->andWhere('e.role = :role')
                ->setParameter('role', $role)
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->render('@TopnodeAuth/switch.html.twig', [
            'roles' => $roles,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * @Route("/logged", name="logged")
     */
    public function logged(Request $request)
    {
        return new JsonResponse([
            'logged' => $this->getUser() ? $this->getUser()->getId() : null,
        ]);
    }

    private function createRecoverForUser(UserInterface $user): UserValidation
    {
        $em = $this->getDoctrine()->getManager();

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

        return $recovery;
    }

    private function authenticateUser(Request $request, UserInterface $user): void
    {
        $token = new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            'public',
            $user->getRoles()
        );

        $this->get('security.token_storage')->setToken($token);

        (new EventDispatcher())->dispatch(
            'security.interactive_login',
            (new InteractiveLoginEvent($request, $token))
        );
    }

    private function codeValidate(string $code): bool
    {
        $em = $this->getDoctrine()->getManager();

        $recovery = $em->getRepository(UserValidation::class)
            ->findOneByIdentifier($code)
        ;

        return is_object($recovery)
            && (new \DateTime()) < $recovery->getExpiresAt()
            && !$recovery->getIsUsed()
        ;
    }

    private function setRecoveryStatusFlag(string $code, bool $isUsed): bool
    {
        $em = $this->getDoctrine()->getManager();

        $recovery = $em->getRepository(UserValidation::class)
            ->findOneByIdentifier($code)
        ;

        if (is_object($recovery)) {
            $em->persist($recovery->setIsUsed($isUsed));
            $em->flush();

            return true;
        }

        return false;
    }
}
