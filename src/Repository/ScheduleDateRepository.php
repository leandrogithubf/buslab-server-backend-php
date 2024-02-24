<?php

namespace App\Repository;

use App\Entity\ScheduleDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ScheduleDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleDate[]    findAll()
 * @method ScheduleDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleDate::class);
    }

    // /**
    //  * @return ScheduleDate[] Returns an array of ScheduleDate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ScheduleDate
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
