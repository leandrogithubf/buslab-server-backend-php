<?php

namespace App\Command;

use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\Schedule;
use App\Entity\ScheduleDate;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScheduleDateImportCommand extends Command
{
    protected static $defaultName = 'app:schedule-date:import';

    public function __construct($name = null, EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('file', InputArgument::REQUIRED, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        $schedulesQb = $this->em
            ->getRepository(Schedule::class)
            ->createQueryBuilder('e')
            ->andWhere('e.tableCode = :code')
            ->andWhere('e.line = :line')
            ->andWhere('e.weekInterval = :weekInterval')
        ;

        $linesQb = $this->em
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->andWhere('e.code = :code')
        ;

        $vehiclesQb = $this->em
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
            ->andWhere('e.prefix = :prefix')
        ;

        $employeeQb = $this->em
            ->getRepository(Employee::class)
            ->createQueryBuilder('e')
            ->andWhere('e.code = :code')
        ;

        $row = 1;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $lines = $linesQb
                    ->setParameter('code', $data[4])
                    ->getQuery()->getResult()
                ;

                try {
                    $vehicle = $vehiclesQb
                        ->setParameter('prefix', $data[3])
                        ->getQuery()->getOneOrNullResult()
                    ;
                } catch (\Exception $e) {
                    dump(1, $data[3]);
                    die();
                }

                try {
                    $driver = $employeeQb
                        ->setParameter('code', $data[1])
                        ->getQuery()->getOneOrNullResult()
                    ;
                } catch (\Exception $e) {
                    dump(2, $data[1]);
                    die();
                }

                foreach ($lines as $line) {
                    $date = \DateTime::createFromFormat('d/m/Y H:i:s', $data[0] . ' 00:00:00');
                    if ($date->format('N') == 7) {
                        $weekInterval = 'SUNDAY';
                    } elseif ($date->format('N') == 6) {
                        $weekInterval = 'SATURDAY';
                    } else {
                        $weekInterval = 'WEEKDAY';
                    }

                    $schedules = $schedulesQb
                        ->setParameter('code', $data[5])
                        ->setParameter('line', $line)
                        ->setParameter('weekInterval', $weekInterval)
                        ->getQuery()->getResult()
                    ;

                    foreach ($schedules as $schedule) {
                        $scheduleDate = (new ScheduleDate())
                            ->setSchedule($schedule)
                            ->setVehicle($vehicle)
                            ->setDriver($driver)
                            ->setDate(\DateTime::createFromFormat('d/m/Y H:i:s', $data[0] . ' 00:00:00'))
                        ;
                        $this->em->persist($scheduleDate);
                    }
                }
            }

            $this->em->flush();

            fclose($handle);
        }

        $io->success('End');

        return 0;
    }
}
