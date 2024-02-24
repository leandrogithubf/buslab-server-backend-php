<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function cnhExist(?string $id = null, string $company, string $cnh): int
    {
        if($id){
            return $this->createQueryBuilder('e')
            ->select('count(e.cnh)')
            ->where('e.id <> :id')
            ->andWhere('e.cnh = :cnh')
            ->setParameter('id', $id)
            ->setParameter('cnh', $cnh)
            ->getQuery()
            ->getSingleScalarResult();
        }
        
        return $this->createQueryBuilder('e')
            ->select('count(e.cnh)')
            ->andWhere('e.cnh = :cnh')
            ->setParameter('cnh', $cnh)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function codeExist(?string $id = null, string $company, string $code): int
    {
        if($id){
            return $this->createQueryBuilder('e')
            ->select('count(e.code)')
            ->where('e.id <> :id')
            ->andWhere('e.company = :company')
            ->andWhere('e.code = :code')
            ->setParameter('id', $id)
            ->setParameter('company', $company)
            ->setParameter('code', $code)
            ->getQuery()
            ->getSingleScalarResult();
        }
        
        return $this->createQueryBuilder('e')
        ->select('count(e.code)')        
        ->andWhere('e.company = :company')
        ->andWhere('e.code = :code')
        ->setParameter('company', $company)
        ->setParameter('code', $code)
        ->getQuery()
        ->getSingleScalarResult();
    }

    // /**
    //  * @return Employee[] Returns an array of Employee objects
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
    public function findOneBySomeField($value): ?Employee
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
