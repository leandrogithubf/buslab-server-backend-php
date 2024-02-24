<?php

namespace App\Security;

use App\Entity\User;
use App\Topnode\AuthBundle\Security\AbstractUserProvider;

class UserProvider extends AbstractUserProvider
{
    public function prepareLoadUserByUsernameQueries(): array
    {
        $list = [];

        $list[] = $this->em->getRepository(User::class)
            ->createQueryBuilder('e')
            ->andWhere('e.documentNumber = :username')
        ;

        $list[] = $this->em->getRepository(User::class)
            ->createQueryBuilder('e')
            ->andWhere('e.cellphone = :username')
        ;

        $list[] = $this->em->getRepository(User::class)
            ->createQueryBuilder('e')
            ->andWhere('e.email = :username')
        ;

        return $list;
    }
}
