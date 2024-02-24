<?php

namespace App\Repository;

use App\Entity\Checkpoint;
use App\Entity\Obd;
use App\Topnode\BaseBundle\Utils\Date\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Checkpoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Checkpoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Checkpoint[]    findAll()
 * @method Checkpoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckpointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Checkpoint::class);
    }

    /**
     * @return Checkpoint[] Returns an array of Checkpoint objects
     */
    public function findByDistance(float $latitude, float $longitude, int $distance = 85, ?Period $period = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('
                6371230 * ACOS( COS( RADIANS(e.latitude) )
                    * COS( RADIANS(:latitude) )
                    * COS( RADIANS(e.longitude - :longitude)) + SIN(RADIANS(e.latitude))
                    * SIN( RADIANS(:latitude) ) )
                AS distance,
                e
            ')
            ->andWhere('e.trip IS NULL')// Para não gerar viagens para pontos já utilizados
            ->andWhere('e.latitude IS NOT NULL')
            ->andWhere('e.longitude IS NOT NULL')
            ->having('distance <= :distance')
            ->orderBy('distance', 'ASC')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('distance', $distance)
        ;

        if ($period instanceof Period) {
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $period->start)
                ->setParameter('endsAt', $period->end)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Checkpoint Returns an array of Checkpoint objects
     */
    public function findOneByDistance(float $latitude, float $longitude, int $distance = 85, ?Period $period = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('
                6371230 * ACOS( COS( RADIANS(e.latitude) )
                    * COS( RADIANS(:latitude) )
                    * COS( RADIANS(e.longitude - :longitude)) + SIN(RADIANS(e.latitude))
                    * SIN( RADIANS(:latitude) ) )
                AS distance,
                e
            ')
            ->andWhere('e.trip IS NULL')// Para não gerar viagens para pontos já utilizados
            ->andWhere('e.latitude IS NOT NULL')
            ->andWhere('e.longitude IS NOT NULL')
            ->having('distance <= :distance')
            ->orderBy('distance', 'ASC')
            ->setMaxResults(1)
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('distance', $distance)
        ;

        if ($period instanceof Period) {
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $period->start)
                ->setParameter('endsAt', $period->end)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Checkpoint[] Returns an array of Checkpoint objects
     */
    public function findNextFewCheckpoints(Checkpoint $checkpoint, int $maxResults = 200)
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.id != :id ')
            ->andWhere('e.date >= :date')
            ->andWhere('e.obd = :obd')
            ->andWhere('e.latitude IS NOT NULL')
            ->andWhere('e.longitude IS NOT NULL')
            ->orderBy('e.date', 'ASC')
            ->andWhere('e.trip IS NULL')// Para não gerar viagens para pontos já utilizados
            ->setParameter('id', $checkpoint->getId())
            ->setParameter('date', $checkpoint->getDate())
            ->setParameter('obd', $checkpoint->getObd())
            ->setMaxResults($maxResults)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getLastCheckpoint(int $obdId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.obd = :obd')
            ->setparameter('obd', $obdId)
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /*
    public function findOneBySomeField($value): ?Checkpoint
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
