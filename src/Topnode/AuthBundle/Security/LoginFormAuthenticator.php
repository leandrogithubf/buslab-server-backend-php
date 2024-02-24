<?php

namespace App\Topnode\AuthBundle\Security;

use App\Entity\LoginAttempts;
use App\Entity\User;
use App\Topnode\AuthBundle\Form\LoginType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use \Symfony\Component\Security\Http\Util\TargetPathTrait;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * App\Topnode\BaseBundle\Security\CaptchaService.
     */
    private $captchaService;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    /**
     * App\Topnode\AuthBundle\Repository\LoginAttemptsRepository.
     */
    private $loginAttempts;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder,
        ContainerInterface $container
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->captchaService = $container->get('tn.security.recaptcha');
        $this->formFactory = $container->get('form.factory');
        $this->loginAttempts = $entityManager->getRepository(LoginAttempts::class);
        $this->authRedirector = $container->get('tn.security.utils.generator.auth_redirection_decisor');
    }

    /**
     * Checks if the method of authentication is valid and the form has been
     * submitted.
     *
     * @param Request $request [description]
     */
    public function supports(Request $request): bool
    {
        $form = $this->formFactory->create(LoginType::class);
        $form->handleRequest($request);

        return $request->isMethod('POST') && $form->isSubmitted();
    }

    /**
     * Returns the user credentials after validating the form and the captcha.
     *
     * @return array The user and password sent by the user
     *
     * @throws CustomUserMessageAuthenticationException
     */
    public function getCredentials(Request $request): array
    {
        $form = $this->formFactory->create(LoginType::class);
        $form->handleRequest($request);

        $data = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['user']
        );

        if (!$form->isValid()) {
            throw new CustomUserMessageAuthenticationException('app.security.authenticator.form_error');
        }

        $ip = $request->getClientIp();

        $qb = $this->loginAttempts->createQueryBuilder('e');

        $attempts = $qb
            ->select('count(e)')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('e.username', ':username'),
                $qb->expr()->eq('e.ip', ':ip')
            ))
            ->andWhere('e.createdAt > :tolerance')
            ->setParameter('tolerance', new \DateTime('10 minutes ago'))
            ->setParameter('username', $data['user'])
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($attempts > 2) {
            if (!$this->captchaService->validate($request->request->get('g-recaptcha-response'))) {
                throw new CustomUserMessageAuthenticationException('app.security.authenticator.captcha_error');
            }
        }

        return [
            'email' => $data['user'],
            'password' => $data['password'],
        ];
    }

    /**
     * From the credentials, try to fetch the user from the database.
     *
     * @param array $credentials The user and password
     *
     * @throws CustomUserMessageAuthenticationException
     */
    public function getUser(
        $credentials,
        UserProviderInterface $userProvider
    ): UserInterface {
        $user = $userProvider->loadUserByUsername($credentials['email']);

        if ($user instanceof UserInterface) {
            return $user;
        }

        throw new CustomUserMessageAuthenticationException('app.security.authenticator.credential_error');
    }

    /**
     * Checks the password with the security default encryption.
     *
     * @param array $credentials
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            throw new CustomUserMessageAuthenticationException('app.security.authenticator.credential_error');
        }

        return true;
    }

    /**
     * Redirects the user to the correct target after successful authentication.
     *
     * @param string $providerKey
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ): RedirectResponse {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if (0 !== strlen($targetPath)) {
            return new RedirectResponse($targetPath);
        }

        return $this->authRedirector->redirect($token->getUser());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception->getToken()) {
            $userName = $exception->getToken()->getCredentials()['email'];
        } else {
            $userName = $request->get('login')['user'];
        }

        $ip = $request->getClientIp();

        $loginAttempt = (new LoginAttempts())
            ->setIp($ip)
            ->setUsername($userName)
        ;

        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush();

        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
    }

    /**
     * Returns the login url from the default route from the auth bundle.
     */
    protected function getLoginUrl(): string
    {
        return $this->router->generate('security_login');
    }
}
