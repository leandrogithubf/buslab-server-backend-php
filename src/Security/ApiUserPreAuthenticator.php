<?php

namespace App\Security;

use App\Entity\LoginAttempts;
use App\Security\Exception\InvalidRecaptchaException;
use App\Utils\RecaptchaValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiUserPreAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    private $invalidRecaptcha = false;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerInterface $container,
        RecaptchaValidatorService $recaptchaValidator
    ) {
        $this->entityManager = $entityManager;
        $this->recaptchaValidator = $recaptchaValidator;
        $this->container = $container;

        $this->authRedirector = $container->get('tn.security.utils.generator.auth_redirection_decisor');
    }

    public function createToken(Request $request, $providerKey)
    {
        $em = $this->entityManager;
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return;
        }

        if (!isset($data['recaptcha_token'])) {
            $data['recaptcha_token'] = '';
        }

        $ip = $request->getClientIp();

        $attempt = (new LoginAttempts())
            ->setIp($ip)
            ->setUsername($data['username'])
        ;
        $em->persist($attempt);
        $em->flush();

        $qb = $em->getRepository(LoginAttempts::class)
            ->createQueryBuilder('e')
        ;

        $attempts = $qb
            ->select('count(e)')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('e.username', ':username'),
                $qb->expr()->eq('e.ip', ':ip')
            ))
            ->andWhere('e.createdAt > :tolerance')
            ->setParameter('tolerance', new \DateTime('10 minutes ago'))
            ->setParameter('username', $data['username'])
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($attempts > 2) {
            if (!$this->recaptchaValidator->validate($data['recaptcha_token'], 'login')) {
                $this->invalidRecaptcha = true;
            }
        }

        return new PreAuthenticatedToken(
            $data['username'],
            $data['password'],
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        if ($this->invalidRecaptcha) {
            throw new InvalidRecaptchaException();
        }

        return ($token instanceof UsernamePasswordToken) && ($token->getProviderKey() === $providerKey)
            || ($token instanceof PreAuthenticatedToken) && ($token->getProviderKey() === $providerKey)
        ;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        return;
    }
}
