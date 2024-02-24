<?php

namespace App\Repository;

use App\Entity\FuelQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FuelQuote|null find($id, $lockMode = null, $lockVersion = null)
 * @method FuelQuote|null findOneBy(array $criteria, array $orderBy = null)
 * @method FuelQuote[]    findAll()
 * @method FuelQuote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FuelQuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FuelQuote::class);
    }

    // /**
    //  * @return FuelQuote[] Returns an array of FuelQuote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FuelQuote
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
