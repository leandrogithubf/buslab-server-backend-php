<?php

namespace App\Repository;

use App\Entity\EventModality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventModality|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventModality|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventModality[]    findAll()
 * @method EventModality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventModalityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventModality::class);
    }

    // /**
    //  * @return EventModality[] Returns an array of EventModality objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EventModality
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
