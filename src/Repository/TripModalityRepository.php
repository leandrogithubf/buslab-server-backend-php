<?php

namespace App\Repository;

use App\Entity\TripModality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TripModality|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripModality|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripModality[]    findAll()
 * @method TripModality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripModalityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripModality::class);
    }

    // /**
    //  * @return TripModality[] Returns an array of TripModality objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TripModality
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
