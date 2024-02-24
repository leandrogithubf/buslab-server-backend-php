<?php

namespace App\Topnode\AuthBundle\Security;

use App\Topnode\AuthBundle\Entity\MappedSuperclass\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class AbstractUserProvider implements UserProviderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    abstract public function prepareLoadUserByUsernameQueries(): array;

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $qbList = $this->prepareLoadUserByUsernameQueries();

        $results = [];
        foreach ($qbList as $qb) {
            $results[] = $qb->setParameter('username', $username)
                ->getQuery()->getOneOrNullResult()
            ;
        }

        $user = null;
        foreach ($results as $result) {
            $user ??= $result;
        }

        if ($user instanceof User) {
            return $user;
        }

        throw new UsernameNotFoundException('Usuário não encontrado');
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        throw new UsernameNotFoundException('Usuário não encontrado');
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class)
    {
        return in_array(UserInterface::class, class_implements($class));
    }
}
