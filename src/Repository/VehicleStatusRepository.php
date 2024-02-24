<?php

namespace App\Repository;

use App\Entity\VehicleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VehicleStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehicleStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehicleStatus[]    findAll()
 * @method VehicleStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleStatus::class);
    }

    // /**
    //  * @return VehicleStatus[] Returns an array of VehicleStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VehicleStatus
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
