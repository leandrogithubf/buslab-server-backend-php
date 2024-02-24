<?php

namespace App\Repository;

use App\Entity\LoginAttempts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoginAttempts|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginAttempts|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginAttempts[]    findAll()
 * @method LoginAttempts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginAttemptsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempts::class);
    }
}
