<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($username): ?User
    {
        if (0 === strlen($username)) {
            return null;
        }

        $qb = $this->createQueryBuilder('e');

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->orX('e.documentNumber = :username'),
                $qb->expr()->orX('e.cellphone = :username'),
                $qb->expr()->orX('e.email = :username'),
            )
        )->setParameter('username', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getPassword($username): ?string
    {
        if (0 === strlen($username)) {
            return null;
        }

        $qb = $this->createQueryBuilder('e');

        $qb->select('e.password')
            ->where(
            $qb->expr()->orX(
                $qb->expr()->orX('e.documentNumber = :username'),
                $qb->expr()->orX('e.cellphone = :username'),
                $qb->expr()->orX('e.email = :username'),
            )
        )->setParameter('username', $username);

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    public function documentExist(?string $userId = null, string $document): int
    {
        if($userId){
            return $this->createQueryBuilder('u')
            ->select('count(u.documentNumber)')
            ->where('u.documentNumber = :document')
            ->andWhere('u.id <> :id')
            ->setParameter('document', $document)
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
        }

        return $this->createQueryBuilder('u')
            ->select('count(u.documentNumber)')
            ->where('u.documentNumber = :document')
            ->setParameter('document', $document)
            ->getQuery()
            ->getSingleScalarResult();        
    }


}
