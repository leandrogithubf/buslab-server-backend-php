<?php

namespace App\Command;

use App\Entity\Line;
use App\Entity\Schedule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScheduleImportCommand extends Command
{
    protected static $defaultName = 'app:schedule:import';

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

        $linesQb = $this->em
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
            ->andWhere('e.code = :code')
            ->andWhere('e.direction = :direction')
        ;

        $row = 1;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $line = $linesQb
                    ->setParameter('code', $data[0])
                    ->setParameter('direction', 'GOING')
                    ->getQuery()->getOneOrNullResult()
                ;

                $schedule = (new Schedule())
                    ->setSequence(1)
                    ->setTableCode($data[1])
                    ->setModality($data[2])
                    ->setStartsAt(\DateTime::createFromFormat('H:i', $data[5]))
                    ->setEndsAt(\DateTime::createFromFormat('H:i', $data[6]) ?? \DateTime::createFromFormat('H:i', $data[5]))
                    ->setWeekInterval($data[7])
                    ->setLine($line)
                    ->setDataValidity(\DateTime::createFromFormat('Y-m-d H:i:s', '2020-12-31 00:00:00'))
                ;

                $schedule->setDescription(
                    $schedule->getTableCode()
                    . ' - ' . $schedule->getLine()->getDescription()
                    . ' - ' . $schedule->getLine()->getDirection($asHuman = true)
                );

                $this->em->persist($schedule);
                $this->em->flush();
            }
            fclose($handle);
        }

        $io->success('End');

        return 0;
    }
}
