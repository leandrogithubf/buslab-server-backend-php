<?php

namespace App\Repository;

use App\Entity\UserValidation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserValidation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserValidation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserValidation[]    findAll()
 * @method UserValidation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserValidation::class);
    }

    public function findOneValidByCode($code)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isUsed = 0')
            ->andWhere('e.identifier = :code')
            ->andWhere('e.expiresAt > :now')
            ->setParameter('now', new \DateTime())
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
