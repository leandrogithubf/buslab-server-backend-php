<?php

namespace App\Repository;

use App\Entity\LinePoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinePoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinePoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinePoint[]    findAll()
 * @method LinePoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinePointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinePoint::class);
    }

    // /**
    //  * @return LinePoint[] Returns an array of LinePoint objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LinePoint
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
