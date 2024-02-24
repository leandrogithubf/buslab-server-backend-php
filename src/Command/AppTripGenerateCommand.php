<?php

namespace App\Command;

use App\Entity\Checkpoint;
use App\Entity\Line;
use App\Entity\LinePoint;
use App\Entity\Schedule;
use App\Entity\Trip;
use App\Entity\TripModality;
use App\Entity\TripStatus;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Utils\Date\Period;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppTripGenerateCommand extends Command
{
    protected static $defaultName = 'app:trip:generate';

    private $em;

    private $container;

    /**
     * Limite de minutos a menos para pesquisar os checkpoints.
     *
     * @var int
     */
    private $minutesTresholdStartsAt = 30;

    /**
     * Limite de minutos a mais para pesquisar os checkpoints.
     *
     * @var int
     */
    private $minutesTresholdEndsAt = 30;

    /**
     * Limite de distância em metros entre um ponto e um checkpoint.
     *
     * @var int
     */
    private $distanceTreshold = 84;

    public function __construct($name = null, ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
        $this->container = $container;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Script to generate trips from checkpints and schedules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setVars();

        // $this->generateLines(); die();
        // $this->byCloseness(); die();

        $now = new \DateTime();

        // Busca as escalas
        $schedules = $this->em
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.dataValidity >= (:now)')
            ->setParameter('now', (clone $now)->setTime(23, 59, 59))
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        // Roda todas as escalas encontradas para pesquisar por viagens
        foreach ($schedules as $key => $schedule) {
            foreach ($schedule->getDates() as $key2 => $scheduleDate) {
                if (is_object($scheduleDate->getTrip())) {
                    // Já possui viagem, não precisa refazer
                    continue;
                }

                // Sempre respeita as datas dos schedules para fazer a busca
                $startsAt = (clone $schedule->getStartsAt())->setDate(
                    $scheduleDate->getDate()->format('Y'),
                    $scheduleDate->getDate()->format('m'),
                    $scheduleDate->getDate()->format('d')
                );
                $endsAt = (clone $schedule->getEndsAt())->setDate(
                    $scheduleDate->getDate()->format('Y'),
                    $scheduleDate->getDate()->format('m'),
                    $scheduleDate->getDate()->format('d')
                );

                // Pega checkpoints elegíveis
                $checkpoints = $this->findCheckpoints(
                    $startsAt,
                    $endsAt,
                    $scheduleDate->getVehicle() ?? $schedule->getVehicle()
                );

                if (count($checkpoints) === 0) {
                    // Não tem checkpoint para esses parâmetros
                    continue;
                }

                $trip = $this->generateTrip($schedule->getLine(), $checkpoints);

                if (!$trip instanceof Trip) {
                    // Não tem viagem para esses parâmetros
                    continue;
                }

                $trip
                    ->setScheduleDate($scheduleDate)
                    ->setStatus($this->tripStatus[TripStatus::DONE])
                    ->setModality($this->tripModality[TripModality::SCHEDULED])
                    ->setLine($schedule->getLine())
                    ->setVehicle($trip->getCheckpoints()->first()->getVehicle())
                    ->setObd($trip->getCheckpoints()->first()->getVehicle()->getObd())
                ;

                $this->em->persist($trip);
            }
        }

        $this->em->flush();
    }

    private function provisorio()
    {
        // Provisorio
        $lines = $this->em
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $dates = [];
        // $startsAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-30 00:00:00');
        $startsAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-08-01 00:00:00');
        $endsAt = \DateTime::createFromFormat('Y-m-d  H:i:s', '2020-10-04 00:00:00');

        while ($startsAt <= $endsAt) {
            $dates[] = [
                'startsAt' => (clone $startsAt),
                'endsAt' => (clone $startsAt)->setTime(23, 59, 59),
            ];

            $startsAt->modify('+1 day');
        }

        foreach ($lines as $line) {
            if ($line->getPoints()->count() === 0) {
                continue;
            }
            echo 'Line: ' . $line->getId() . "\n";
            foreach ($dates as $date) {
                echo '    Date: ' . $date['startsAt']->format('Y-m-d') . "\n";

                $checkpoints = $this->em
                    ->getRepository(Checkpoint::class)
                    ->createQueryBuilder('e')
                    ->andWhere('e.latitude IS NOT NULL')
                    ->andWhere('e.longitude IS NOT NULL')
                    ->andWhere('e.date BETWEEN :start AND :end')
                    ->setParameter(':start', $date['startsAt'])
                    ->setParameter(':end', $date['endsAt'])
                    ->addOrderBy('e.date', 'ASC')
                    ->addOrderBy('e.id', 'ASC')
                    ->getQuery()
                    ->getResult()
                ;

                if (count($checkpoints) == 0) {
                    continue;
                }
                $trip = $this->generateTrip(
                    $line,
                    $checkpoints
                );

                if (!is_object($trip)) {
                    continue;
                }

                $trip
                    ->setModality($this->tripModality[TripModality::UNSCHEDULED])
                    ->setLine($line)
                    ->setVehicle($trip->getCheckpoints()->first()->getVehicle())
                    ->setObd($trip->getCheckpoints()->first()->getVehicle()->getObd())
                ;

                $this->em->persist($trip);
                $this->em->flush();
            }
        }
        $this->em->flush();
    }

    private function generateLines()
    {
        $lines = $this->em
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        foreach ($lines as $line) {
            if (count($line->getPoints()) === 0) {
                continue;
            }

            $currentPoint = $line->getPoints()->first();
            $nextPoint = $line->getPoints()->next();

            // $startAt = new \DateTime();
            $startAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-10-01 00:00:00');
            $endsAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-10-01 00:00:00');

            $period = (new Period($startAt, $endsAt))->completeDay();

            $checkpoints = $this->em
                ->getRepository(Checkpoint::class)
                ->findByDistance(
                    $currentPoint->getLatitude(),
                    $currentPoint->getLongitude(),
                    $distance = 85,
                    $period
                )
            ;

            if (count($checkpoints) === 0) {
                continue;
            }

            $max = 0;
            foreach ($checkpoints as $checkpointData) {
                $nextCheckpoints = $this->em
                    ->getRepository(Checkpoint::class)
                    ->findNextFewCheckpoints($checkpointData[0])
                ;

                if ($max > 1) {
                    continue;
                }

                $lineAux = (new Line())
                    ->setCode('TEST' . $max . '-' . $line->getCode() . '-' . $line->getDirection())
                    ->setDirection($line->getDirection())
                    ->setMaxSpeed(40)
                    ->setDescription('Teste: ' . $line->getLabel())
                    ->setPassage(0)
                    ->setCompany($line->getCompany())
                ;

                $this->em->persist($lineAux);
                $this->em->flush();

                $sequence = 1;
                foreach ($nextCheckpoints as $key => $checkpoint) {
                    if ($key % 5 !== 0) {
                        continue;
                    }

                    $lineAuxPoint = (new LinePoint())
                        ->setLatitude($checkpoint->getLatitude())
                        ->setLongitude($checkpoint->getLongitude())
                        ->setSequence(++$sequence)
                        ->setLine($lineAux)
                    ;
                    $this->em->persist($lineAuxPoint);
                }
                $this->em->flush();
                ++$max;
                continue;
            }
        }
    }

    private function byCloseness()
    {
        $lines = $this->em
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        foreach ($lines as $line) {
            if (count($line->getPoints()) === 0) {
                continue;
            }

            $currentPoint = $line->getPoints()->first();
            $nextPoint = $line->getPoints()->next();

            // $startAt = new \DateTime();
            $startAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-10-01 00:00:00');
            $endsAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-10-01 00:00:00');

            $period = (new Period($startAt, $endsAt))->completeDay();

            $checkpoint = $this->em
                ->getRepository(Checkpoint::class)
                ->findOneByDistance(
                    $currentPoint->getLatitude(),
                    $currentPoint->getLongitude(),
                    $distance = 85,
                    $period
                )
            ;

            if (is_null($checkpoint)) {
                continue;
            }

            $nextCheckpoints = $this->em
                ->getRepository(Checkpoint::class)
                ->findNextFewCheckpoints($checkpoint[0])
            ;

            $distanceCurrentAux = $this->distance(
                $currentPoint->getLatitude(),
                $currentPoint->getLongitude(),
                $checkpoint[0]->getLatitude(),
                $checkpoint[0]->getLongitude()
            );

            $distanceNextAux = $this->distance(
                $nextPoint->getLatitude(),
                $nextPoint->getLongitude(),
                $checkpoint[0]->getLatitude(),
                $checkpoint[0]->getLongitude()
            );

            $result = [
                $currentPoint->getId() => [
                    $checkpoint[0],
                ],
            ];
            $stack = [
                $checkpoint[0],
            ];

            $farCount = 0;
            foreach ($nextCheckpoints as $checkpoint) {
                // Valida se chegamos no último ponto
                if ($line->getPoints()->indexOf($currentPoint) === false) {
                    break;
                }

                $distanceCurrent = $this->distance(
                    $currentPoint->getLatitude(),
                    $currentPoint->getLongitude(),
                    $checkpoint->getLatitude(),
                    $checkpoint->getLongitude()
                );

                if ($distanceCurrent <= 60) {
                    // Salva esse ponto como sendo próximo do ponto de parada da linha
                    if (!array_key_exists($currentPoint->getId(), $result)) {
                        $result[$currentPoint->getId()] = [];
                    }
                    $result[$currentPoint->getId()][] = $checkpoint;

                    // Atualiza o ponto atual e o próximo
                    $currentPoint = $nextPoint;
                    $nextPoint = $line->getPoints()->next();
                    // dump($currentPoint->getSequence(), $nextPoint->getSequence());

                    $stack[] = $checkpoint; // Salva o ponto atual na pilha de pontos da viagem
                    $farCount = 0; // zera a contagem de distanciamento
                } elseif ($line->getPoints()->indexOf($nextPoint) !== false) {
                    $distanceNext = $this->distance(
                        $nextPoint->getLatitude(),
                        $nextPoint->getLongitude(),
                        $checkpoint->getLatitude(),
                        $checkpoint->getLongitude()
                    );

                    if ($distanceCurrent > $distanceCurrentAux && $distanceNext < $distanceNextAux) {
                        // A distância do ponto atual está diminuindo e estamos nos aproximando do próximo
                        $stack[] = $checkpoint; // Salva o ponto atual na pilha de pontos da viagem
                        $farCount = 0; // zera a contagem de distanciamento
                    } else {
                        // Estamos indo na direção incorreta para ambos os pontos (atual e próximo)
                        ++$farCount;
                    }

                    // Atualiza variável para comparar distanciamento do próximo ponto
                    $distanceNextAux = $distanceNext;
                } else {
                    ++$farCount;
                }

                // Atualiza variável para comparar distanciamento do ponto atual
                $distanceCurrentAux = $distanceCurrent;

                if ($farCount > 9) {
                    break;
                }
            }

            if (count($result) === $line->getPoints()->count()) {
                $trip = (new Trip())
                    ->setStartsAt($stack[0]->getDate())
                    ->setEndsAt($stack[count($stack) - 1]->getDate())
                    ->setStatus($this->tripStatus[TripStatus::DONE])
                    ->setModality($this->tripModality[TripModality::UNSCHEDULED])
                    ->setLine($line)
                    ->setVehicle($stack[0]->getVehicle())
                    ->setObd($stack[0]->getVehicle()->getObd())
                ;

                foreach ($stack as $checkpoint) {
                    $trip->addCheckpoint($checkpoint);
                }

                $this->em->persist($trip);
            }
        }

        $this->em->flush();
    }

    private function setVars(): void
    {
        $tripStatusAux = $this->em
            ->getRepository(TripStatus::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $this->tripStatus = [];
        foreach ($tripStatusAux as $status) {
            $this->tripStatus[$status->getId()] = $status;
        }

        $tripModalityAux = $this->em
            ->getRepository(TripModality::class)
            ->createQueryBuilder('e')
            ->getQuery()
            ->getResult()
        ;

        $this->tripModality = [];
        foreach ($tripModalityAux as $modality) {
            $this->tripModality[$modality->getId()] = $modality;
        }
    }

    /**
     * Busca pelos checkpoints dentro daquele período de tempo e veículo passado.
     *
     * @param Vehicle $vehicle
     *
     * @return Checkpoint[]
     */
    private function findCheckpoints(
        \DateTimeInterface $startsAt,
        \DateTimeInterface $endsAt,
        ?Vehicle $vehicle
    ): array {
        $startsAt->sub(new \DateInterval('PT20M'));
        $endsAt->add(new \DateInterval('PT20M'));

        $qb = $this->em
            ->getRepository(Checkpoint::class)
            ->createQueryBuilder('e')
            ->andWhere('e.trip IS NULL')
            ->andWhere('e.latitude IS NOT NULL')
            ->andWhere('e.longitude IS NOT NULL')
            ->andWhere('e.date BETWEEN :start AND :end')
            ->setParameter(':start', $startsAt)
            ->setParameter(':end', $endsAt)
            ->addOrderBy('e.date', 'ASC')
            ->addOrderBy('e.id', 'ASC')
        ;

        if (is_object($vehicle)) {
            $qb
                ->andWhere('e.vehicle = :vehicle')
                ->setParameter(':vehicle', $vehicle)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Calcula a distância entre os pontos e os checkpoints e os relaciona com
     * os mais próximos.
     *
     * @param array $pontos
     *
     * @return array
     */
    private function generateTrip(Line $line, array $checkpoints): ?Trip
    {
        $stack = [];
        $result = [];
        foreach ($line->getPoints() as $point) {
            if ($line->getPoints()->indexOf($point) == 0 || $line->getPoints()->indexOf($point) == $line->getPoints()->count() - 1) {// Se inicial ou final, o limite diminui.
                $min = 65;
            } else {
                $min = 85;
            }

            foreach ($checkpoints as $key => $checkpoint) {
                $distance = $this->distance(
                    $point->getLatitude(),
                    $point->getLongitude(),
                    $checkpoint->getLatitude(),
                    $checkpoint->getLongitude()
                );

                if ($distance <= $min) {
                    if (!array_key_exists($point->getId(), $result)) {
                        $result[$point->getId()] = [];
                    }
                    $result[$point->getId()][] = $checkpoint;

                    $stack[] = $checkpoint;
                    unset($checkpoints[$key]);
                }

                // Ignora todos os checkpoints até achar o primeiro ponto
                if (count($stack) === 0) {
                    unset($checkpoints[$key]);
                    continue;
                }
            }
        }

        if (count($result) === 0) {
            return null;
        }

        $first = null;
        $aux = reset($result);
        foreach ($aux as $checkpoint) {
            if (is_null($first) || $first->getDate() > $checkpoint->getDate()) {
                $first = $checkpoint;
            }
        }

        $last = null;
        $aux = end($result);
        foreach ($aux as $checkpoint) {
            if (is_null($last) || $last->getDate() < $checkpoint->getDate()) {
                $last = $checkpoint;
            }
        }

        foreach ($checkpoints as $checkpoint) {
            if ($first->getDate() <= $checkpoint->getDate() && $last->getDate() >= $checkpoint->getDate()) {
                $stack[] = $checkpoint;
            }
        }

        $trip = (new Trip())
            ->setStartsAt($first->getDate())
            ->setEndsAt($last->getDate())
            ->setStatus($this->tripStatus[TripStatus::DONE])
        ;

        foreach ($stack as $checkpoint) {
            $trip->addCheckpoint($checkpoint);
        }

        return $trip;
    }

    /**
     * Dado um array de checkpoints com a distância, acha o com menor distância
     * e o retorna (junto com a distância).
     *
     * @return array
     */
    private function findClosest(array $data): ?array
    {
        $closest = null;

        $min = null;
        foreach ($data as $row) {
            if (is_null($min)) {
                $closest = $row;
            } else {
                if ($min < $row['distance']) {
                    $closest = $row;
                }
            }
        }

        return $closest;
    }

    /**
     * Calcula a distância em metros entre dois pontos geográficos com lat long.
     *
     * @return float Disância em metros entre um ponto e outro
     */
    private function distance(float $lat1, float $long1, float $lat2, float $long2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($long1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($long2);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        return atan2(sqrt($a), $b) * 6371230;
    }
}
