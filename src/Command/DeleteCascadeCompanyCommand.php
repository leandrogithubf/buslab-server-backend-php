<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\CompanyPlace;
use App\Entity\Consumption;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\FuelQuote;
use App\Entity\Line;
use App\Entity\LinePoint;
use App\Entity\Obd;
use App\Entity\ParameterConfiguration;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteCascadeCompanyCommand extends Command
{
    protected static $defaultName = 'app:delete:cascade:company';

    private $em;

    public function __construct($name = null, ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
        $this->container = $container;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Script para apagar dados de empresas que são excluídas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->getFilters()->disable('tn.doctrine.listener.deactivate');

        $companies = $this->em
            ->getRepository(Company::class)
            ->createQueryBuilder('e')
            ->andWhere('e.isActive = :isActive')
            ->setParameter('isActive', false)
            ->getQuery()
            ->getResult()
        ;

        foreach ($companies as $key => $company) {
            $obd = $this->em
                ->getRepository(Obd::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->getQuery()
                ->getResult()
            ;

            $vehicle = $this->em
                ->getRepository(Vehicle::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $employee = $this->em
                ->getRepository(Employee::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $fuel = $this->em
                ->getRepository(FuelQuote::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $lines = $this->em
                ->getRepository(Line::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $linePoints = $this->em
                ->getRepository(LinePoint::class)
                ->createQueryBuilder('e')
                ->andWhere('e.line IN (:lines)')
                ->setParameter('lines', $lines)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $parameter = $this->em
                ->getRepository(ParameterConfiguration::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $trip = $this->em
                ->getRepository(Trip::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $users = $this->em
                ->getRepository(User::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $events = $this->em
                ->getRepository(Event::class)
                ->createQueryBuilder('e')
                ->andWhere('e.vehicle IN (:vehicle)')
                ->setParameter('vehicle', $vehicle)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $companyPlace = $this->em
                ->getRepository(CompanyPlace::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $consumption = $this->em
                ->getRepository(Consumption::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $schedules = $this->em
                ->getRepository(Schedule::class)
                ->createQueryBuilder('e')
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $company)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            $schedulesDate = $this->em
                ->getRepository(ScheduleDate::class)
                ->createQueryBuilder('e')
                ->andWhere('e.schedule IN (:schedules)')
                ->setParameter('schedules', $schedules)
                ->andWhere('e.isActive = :isActive')
                ->setParameter('isActive', true)
                ->getQuery()
                ->getResult()
            ;

            if (count($schedulesDate) > 0) {
                echo 'apagando data de escalas' . PHP_EOL;
                foreach ($schedulesDate as $key => $scheduleDate) {
                    $this->em->persist($scheduleDate->setIsActive(false));
                    $this->em->flush();
                }
            }

            if (count($schedules) > 0) {
                echo 'apagando escalas' . PHP_EOL;
                foreach ($schedules as $key => $schedule) {
                    $this->em->persist($schedule->setIsActive(false));
                    $this->em->flush();
                }
            }

            if (count($consumption) > 0) {
                echo 'apagando consumo' . PHP_EOL;
                foreach ($consumption as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($companyPlace) > 0) {
                echo 'apagando company places' . PHP_EOL;
                foreach ($companyPlace as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($events) > 0) {
                echo 'apagando eventos' . PHP_EOL;
                foreach ($events as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                    $this->em->flush();
                }
            }

            if (count($trip) > 0) {
                echo 'apagando viagens' . PHP_EOL;
                foreach ($trip as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                    $this->em->flush();
                }
            }

            if (count($vehicle) > 0) {
                echo 'apagando veiculos' . PHP_EOL;
                foreach ($vehicle as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                    $this->em->persist($item->setObd(null));
                }
            }
            $this->em->flush();

            if (count($obd) > 0) {
                echo 'apagando obds' . PHP_EOL;
                foreach ($obd as $key => $item) {
                    $this->em->persist($item->setCompany(null));
                }
            }
            $this->em->flush();

            if (count($employee) > 0) {
                echo 'apagando empregados' . PHP_EOL;
                foreach ($employee as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($fuel) > 0) {
                echo 'apagando fuel' . PHP_EOL;
                foreach ($fuel as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($linePoints) > 0) {
                echo 'apagando pontos das linhas' . PHP_EOL;
                foreach ($linePoints as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($lines) > 0) {
                echo 'apagando linhas' . PHP_EOL;
                foreach ($lines as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($parameter) > 0) {
                echo 'apagando parametros de empresa' . PHP_EOL;
                foreach ($parameter as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();

            if (count($users) > 0) {
                echo 'apagando usuarios' . PHP_EOL;
                foreach ($users as $key => $item) {
                    $this->em->persist($item->setIsActive(false));
                }
            }
            $this->em->flush();
        }
        echo 'todos os dados apagados!' . PHP_EOL;
    }
}
