<?php

namespace App\Controller\Api\Adm;

use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\LinePoint;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Entity\ScheduleDate;
use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use App\Form\Api\Adm\ObdSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use PHPExcel;
use PHPExcel_IOFactory;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * @Route(
 *     "/api/adm/report",
 *     name="api_adm_report_"
 * )
 */
class ReportController extends AbstractApiController
{
    /**
     * @Route(
     *     "/total_frota",
     *     name="total_frota",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function totalFrota(
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        // Totalização de frota, km percorrida e linhas
        $qbV = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
        ;

        $qbL = $this->getEntityManager()
            ->getRepository(Line::class)
            ->createQueryBuilder('e')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qbV->andWhere('e.company = :company')->setParameter('company', $company);
            $qbL->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $qbVehicle = $qbV->getQuery()->getResult();
        $qbLine = $qbL->getQuery()->getResult();

        $data = $formFactory->getFormHandled()->getData();
        $startsAt = null;
        $endsAt = null;

        $now = new \DateTime();
        if (isset($data['start']) && $data['start'] != null && $data['end'] === null) {
            $startsAt = $data['start'];
            $endsAt = (clone $now);
        }

        if (isset($data['start']) && $data['start'] === null && $data['end'] != null) {
            $startsAt = (clone $now);
            $endsAt = $data['end'];
        }

        if (isset($data['start']) && $data['start'] != null && $data['end'] != null) {
            $startsAt = $data['start'];
            $endsAt = $data['end'];
        }

        $qb = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        if (!is_null($startsAt) && !is_null($endsAt)) {
            $qb
                ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $endsAt)
            ;
        }

        if (isset($data['days']) && !is_null($data['days'])) {
            $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
            $qb
                ->andWhere('e.starts_at BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $now)
            ;
        }

        if (isset($data['sequence']) && count($data['sequence']) > 0 && !is_null($data['sequence'][0])) {
            $query = '';
            foreach ($data['sequence'] as $key => $date) {
                if ($key > 0) {
                    $query .= ' or ';
                }
                $query .= 'e.starts_at between :date' . $key . 'start and :date' . $key . 'end';

                $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
            }

            $qb->andWhere($query);
        }

        $qbTrip = $qb->getQuery()->getResult();

        $distance = 0;
        foreach ($qbTrip as $key => $viagem) {
            if ($viagem->getReport()) {
                $distance += $viagem->getReport()->getDistance();
            }
        }

        // exportação em csv
        $now = new \DateTime();
        // $monitoramentoFrotaCsv = fopen(__DIR__ . '/../monitoramento_frota.csv', 'w');
        $monitoramentoFrotaCsv = fopen('./assets/csv/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.csv', 'w');

        fputcsv($monitoramentoFrotaCsv, ['Monitoramento de Frota']);
        fputcsv($monitoramentoFrotaCsv, ['Frota atual:', count($qbVehicle)]);
        fputcsv($monitoramentoFrotaCsv, []);

        fputcsv($monitoramentoFrotaCsv, ['Total de linhas:', count($qbLine)]);
        fputcsv($monitoramentoFrotaCsv, []);

        fputcsv($monitoramentoFrotaCsv, ['Total de km percorridos(em viagens):', $distance]);
        fputcsv($monitoramentoFrotaCsv, []);

        fclose($monitoramentoFrotaCsv);

        // local
        // $entityCSV = FileHandler::recordFileBD('./assets/csv/totalizacao_de_frota-'.$now->format('d-m-Y H:i:s').'.csv');
        // servidor
        $entityCSV = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/csv/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.csv');
        if (is_object($this->getUser()->getCompany())) {
            $entityCSV->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityCSV);

        // //exportação em Excel

        // include("PHPExcel/Classes/PHPExcel/IOFactory.php");
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setDelimiter(','); // define que a separação dos dados é feita por ponto e vírgula
        $objReader->setInputEncoding('UTF-8'); // habilita os caracteres latinos.
        $objPHPExcel = $objReader->load('./assets/csv/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.csv'); // indica qual o arquivo CSV que será convertido
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('./assets/excel/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.xls'); // Resultado da conversão; um arquivo do EXCEL

        // local
        // $entityXLS = FileHandler::recordFileBD('./assets/excel/totalizacao_de_frota-'.$now->format('d-m-Y H:i:s').'.xls');
        // servidor
        $entityXLS = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/excel/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.xls');
        if (is_object($this->getUser()->getCompany())) {
            $entityXLS->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityXLS);

        // //exportação em pdf
        $html = '';

        $html .= '<h1>Total de Frota</h1><br>';
        $html .= 'Frota atual: ' . count($qbVehicle) . '<br>';
        $html .= '<br>';
        $html .= 'Total de km percorridos(em viagens): ' . $distance . '<br>';
        $html .= '<br>';
        $html .= "'Total de linhas:" . count($qbLine) . '<br>';

        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($html);
        // local
        // $html2pdf->output('/var/www/html/painel.buslab.com.br-sistema-monolith/public/assets/pdf/totalizacao_de_frota-'.$now->format('d-m-Y H:i:s').'.pdf', 'F'); //Generate and load the PDF in the browser.
        // servidor
        $html2pdf->output('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.pdf', 'F'); // Generate and load the PDF in the browser.

        // local
        // $entityPDF = FileHandler::recordFileBD('./assets/pdf/totalizacao_de_frota-'.$now->format('d-m-Y H:i:s').'.pdf');
        // servidor
        $entityPDF = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.pdf');
        if (is_object($this->getUser()->getCompany())) {
            $entityPDF->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityPDF);

        $result = [
            'csv' => './assets/csv/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.csv',
            'xls' => './assets/excel/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.xls',
            'pdf' => './assets/pdf/totalizacao_de_frota-' . $now->format('d-m-Y H:i:s') . '.pdf',
        ]
        ;

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/viagens_frota",
     *     name="viagens_frota",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function viagensFrota(
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        $qbV = $this->getEntityManager()
            ->getRepository(Vehicle::class)
            ->createQueryBuilder('e')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qbV->andWhere('e.company = :company')->setParameter('company', $company);
        }

        $vehicles = $qbV->getQuery()->getResult();

        $qbScheduleDates = $this->getEntityManager()
            ->getRepository(ScheduleDate::class)
            ->createQueryBuilder('e')
            ->andWhere('e.vehicle IN (:vehicles)')
            ->setParameter('vehicles', $vehicles)
            ->getQuery()
            ->getResult()
        ;

        $qbTrip = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->andWhere('e.scheduleDate IN (:scheduleDates)')
            ->setParameter('scheduleDates', $qbScheduleDates)
            ->getQuery()
            ->getResult()
        ;

        $frota = [];
        foreach ($qbScheduleDates as $key => $qbScheduleDate) {
            if (!is_null($qbScheduleDate) && !in_array($qbScheduleDate->getVehicle(), $frota)) {
                array_push($frota, $qbScheduleDate->getVehicle());
            }
        }

        $frotaAlocada = count($frota);
        $previsto = count($qbScheduleDates);
        $realizado = count($qbTrip);

        $result = [
            'frotaAlocada' => $frotaAlocada,
            'previsto' => $previsto,
            'realizado' => $realizado,
        ];

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/frequencia_pontos_parada",
     *     name="frequencia_pontos_parada",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function frequenciaPontosParada(
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        $data = $formFactory->getFormHandled()->getData();
        $startsAt = null;
        $endsAt = null;

        $now = new \DateTime();
        if (isset($data['start']) && $data['start'] != null && $data['end'] === null) {
            $startsAt = $data['start'];
            $endsAt = (clone $now);
        }

        if (isset($data['start']) && $data['start'] === null && $data['end'] != null) {
            $startsAt = (clone $now);
            $endsAt = $data['end'];
        }

        if (isset($data['start']) && $data['start'] != null && $data['end'] != null) {
            $startsAt = $data['start'];
            $endsAt = $data['end'];
        }

        $qb = $this->getEntityManager()
            ->getRepository(Trip::class)
            ->createQueryBuilder('e')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'e.vehicle = vehicle.id')
            ->innerJoin(Line::class, 'line', 'WITH', 'e.line = line.id')
            ->innerJoin(Employee::class, 'driver', 'WITH', 'e.driver = driver.id')
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('e.company = :company')->setParameter('company', $company);
        }

        if (!is_null($startsAt) && !is_null($endsAt)) {
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $endsAt)
            ;
        }

        if (isset($data['days']) && !is_null($data['days'])) {
            $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
            $qb
                ->andWhere('e.date BETWEEN :startsAt AND :endsAt')
                ->setParameter('startsAt', $startsAt)
                ->setParameter('endsAt', $now)
            ;
        }

        if (isset($data['sequence']) && count($data['sequence']) > 0 && !is_null($data['sequence'][0])) {
            $query = '';
            foreach ($data['sequence'] as $key => $date) {
                if ($key > 0) {
                    $query .= ' or ';
                }
                $query .= 'e.date between :date' . $key . 'start and :date' . $key . 'end';

                $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
            }

            $qb->andWhere($query);
        }

        if (isset($data['line']) && count($data['line']) > 0) {
            $qb
                ->andWhere('e.line = (:line)')
                ->setParameter('line', $data['line'])
            ;
        }

        if (isset($data['driver']) && count($data['driver']) > 0) {
            $qb
                ->andWhere('e.driver = (:employee)')
                ->setParameter('employee', $data['driver'])
            ;
        }

        if (isset($data['vehicle']) && count($data['vehicle']) > 0) {
            $qb
                ->andWhere('e.vehicle = (:vehicle)')
                ->setParameter('vehicle', $data['vehicle'])
            ;
        }

        if (isset($data['company']) && count($data['company']) > 0) {
            $qb
                ->andWhere('e.company = (:company)')
                ->setParameter('company', $data['company'])
            ;
        }

        $trips = $qb->getQuery()->getResult();

        $lines = [];
        $reports = [];
        foreach ($trips as $trip) {
            if (!in_array($trip->getLine(), $lines)) {
                array_push($lines, $trip->getLine());
            }
            if (!is_null($trip->getReport())) {
                array_push($reports, $trip->getReport());
            }
        }

        $pointLines = $this->getEntityManager()
            ->getRepository(LinePoint::class)
            ->createQueryBuilder('e')
            ->andWhere('e.line IN (:lines)')
            ->setParameter('lines', $lines)
            ->getQuery()
            ->getResult()
        ;

        $qtdReports = count($reports);
        $stops = [];
        foreach ($reports as $report) {
            array_push($stops, json_decode($report->getStops()));
        }

        $preResult = [];
        foreach ($stops as $key => $stop) {
            if ($stop['distance'] < 15) {
                if (isset($preResult[$stop['point']->getIdentifier()])) {
                    $preResult += [$stop['point']->getIdentifier() => 1];
                } else {
                    ++$preResult[$stop['point']->getIdentifier()];
                }
            }
        }

        $result = [];
        foreach ($pointLines as $key => $pointLine) {
            $temp = [$pointLine, ($qtdReports / ($preResult[$pointLine->getIdentifier()])) * 100];
            array_push($result, $temp);
        }

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/report_two",
     *     name="list_two",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function reportTwoAction(
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/report_three",
     *     name="list_three",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function reportThreeAction(
        ObdSearchTypeFactory $formFactory
    ): JsonResponse {
        return $this->response(200, $this->paginate($qb));
    }
}
