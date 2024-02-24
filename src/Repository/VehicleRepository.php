<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function countPrefixPerCity(int $prefix, int $cityId, int $id = null)
    {
        if($id){
            return $this->createQueryBuilder('v')
            ->select('count(v.prefix)')
            ->innerJoin(Company::class, 'company', 'WITH', 'v.company = company.id')
            ->where('v.prefix = :prefix')
            ->andWhere('company.city = :city')
            ->andWhere('v.id <> :id')
            ->setParameter('prefix', $prefix)
            ->setParameter('city', $cityId)
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
        }else{
            return $this->createQueryBuilder('v')
            ->select('count(v.prefix)')
            ->innerJoin(Company::class, 'company', 'WITH', 'v.company = company.id')
            ->where('v.prefix = :prefix')
            ->andWhere('company.city = :city')
            ->setParameter('prefix', $prefix)
            ->setParameter('city', $cityId)
            ->getQuery()
            ->getSingleScalarResult();
        }        
    }
    // /**
    //  * @return Vehicle[] Returns an array of Vehicle objects
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
    public function findOneBySomeField($value): ?Vehicle
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
