<?php

namespace App\Repository;

use App\Entity\Obd;
/* use App\Entity\Checkpoint; */
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Obd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Obd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Obd[]    findAll()
 * @method Obd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Obd::class);
    }

  
    // /**
    //  * @return Obd[] Returns an array of Obd objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Obd
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
