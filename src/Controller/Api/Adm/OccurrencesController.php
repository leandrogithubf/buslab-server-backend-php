<?php

namespace App\Controller\Api\Adm;

use App\Entity\Checkpoint;
use App\Entity\Event;
use App\Entity\EventModality;
use App\Entity\EventStatus;
use App\Entity\Trip;
use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use App\Form\Api\Adm\EventEditTypeFactory;
use App\Form\Api\Adm\EventTypeFactory;
use App\Form\Api\Adm\OccurrenceSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use PHPExcel;
use PHPExcel_IOFactory;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * @Route(
 *     "/api/adm/occurrences",
 *     name="api_adm_occurrence_"
 * )
 */
class OccurrencesController extends AbstractApiController
{
    /**
     * @Route(
     *     "/list",
     *     name="list",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function listAction(
        OccurrenceSearchTypeFactory $formFactory
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
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->leftJoin('e.vehicle', 'vehicle')
            ->leftJoin('e.category', 'occurrenceType')
            ->leftJoin('e.trip', 'trip')
            ->leftJoin('e.sector', 'sector')
            ->leftJoin('e.status', 'eventStatus')
            ->leftJoin('e.driver', 'collaborators')
            ->setParameter('modality', EventModality::OCCURRENCE)
            ->addOrderBy('e.protocol', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb->andWhere('vehicle.company = :company')->setParameter('company', $company);
        }

        $form = $formFactory->getFormHandled();

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->responseFormError($form->getErrors(true));
            }

            $searchData = $form->getData();

            if (!is_null($startsAt) && !is_null($endsAt)) {
                $qb
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
                    ->setParameter('startsAt', $startsAt)
                    ->setParameter('endsAt', $endsAt)
                ;
            }

            if (isset($data['days']) && !is_null($data['days'])) {
                $startsAt = (clone $now)->sub(new \DateInterval('P' . $data['days'] . 'D'))->setTime(0, 0, 0);
                $qb
                    ->andWhere('e.start BETWEEN :startsAt AND :endsAt')
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
                    $query .= 'e.start between :date' . $key . 'start and :date' . $key . 'end';

                    $qb->setParameter('date' . $key . 'start', (clone $date)->setTime(0, 0, 0));
                    $qb->setParameter('date' . $key . 'end', (clone $date)->setTime(23, 59, 59));
                }

                $qb->andWhere($query);
            }

            if (count($searchData['collaborators']) > 0) {
                $qb
                    ->andWhere('e.driver in (:driver)')
                    ->setParameter('driver', $searchData['collaborators'])
                ;
            }

            if (strlen($searchData['protocol']) > 0) {
                $qb
                    ->andWhere('e.protocol LIKE :protocol')
                    ->setParameter('protocol', '%' . $searchData['protocol'] . '%')
                ;
            }

            if (count($searchData['vehicle']) > 0) {
                $qb
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
                ;
            }

            if (count($searchData['trip']) > 0) {
                $qb
                    ->andWhere('e.trip in (:trips)')
                    ->setParameter('trips', $searchData['trip'])
                ;
            }

            if (count($searchData['sector']) > 0) {
                $qb
                    ->andWhere('e.sector in (:sectors)')
                    ->setParameter('sectors', $searchData['sector'])
                ;
            }

            if (count($searchData['occurrenceType']) > 0) {
                $qb
                    ->andWhere('e.category in (:category)')
                    ->setParameter('category', $searchData['occurrenceType'])
                ;
            }

            if ($searchData['dateStart']) {
                $qb
                    ->andWhere('e.start >= :dateStart')
                    ->setParameter('dateStart', $searchData['dateStart'])
                ;
            }

            if ($searchData['dateEnd']) {
                $qb
                    ->andWhere('e.start <= :dateEnd')
                    ->setParameter('dateEnd', $searchData['dateEnd'])
                ;
            }
        }

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/new",
     *     name="new",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function newAction(
        EventTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Event();
        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 1')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $status = $this->getEntityManager()
            ->getRepository(EventStatus::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 4')
            ->getQuery()
            ->getOneOrNullResult()
        ;
        $entity = $entity->setModality($modality);
        $entity = $entity->setStatus($status);

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }
        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/{identifier}/show",
     *     name="show",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function showAction(Event $entity): JsonResponse
    {
        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/{identifier}/edit",
     *     name="edit",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function editAction(
        Event $entity,
        EventEditTypeFactory $formFactory
    ): JsonResponse {
        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }

        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/{identifier}/remove",
     *     name="remove",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function removeAction(Event $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/trip-vinculate",
     *     name="editrip_vinculate",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function tripVinculateAction(
        Trip $trip,
        EventTypeFactory $formFactory
    ): JsonResponse {
        $entity = new Event();
        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 1')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $status = $this->getEntityManager()
            ->getRepository(EventStatus::class)
            ->createQueryBuilder('e')
            ->andWhere('e.id = 4')
            ->getQuery()
            ->getOneOrNullResult()
        ;
        $entity = $entity->setModality($modality);
        $entity = $entity->setStatus($status);
        $entity = $entity->setTrip($trip);

        $form = $formFactory->setData($entity)->getFormHandled();

        if (!$form->isSubmitted()) {
            return $this->responseError(400, 'app.page_errors.generic_error');
        }
        if (!$form->isValid()) {
            return $this->responseFormError($form->getErrors(true));
        }

        $this->persist($entity);

        return $this->response(200, $entity);
    }

    /**
     * @Route(
     *     "/{identifier}/list-trip",
     *     name="list-trip",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
     *         "identifier"="[\w\-\_]{15}",
     *         "_format"="json"
     *     }
     * )
     */
    public function listTripAction(
        Trip $trip
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.trip = :trip')
            ->setParameter('trip', $trip)
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach ($qb as $event) {
            $vehicle = $event->getVehicle();
            $start = $event->getStart();

            if (is_null($event->getStart())) {
                $end = $event->getTrip()->getEndsAt();
            } else {
                $end = $event->getStart();
            }

            $checkpoints = $this->getEntityManager()
                ->getRepository(Checkpoint::class)
                ->createQueryBuilder('e')
                ->andWhere('e.vehicle = :vehicle')
                ->andWhere('e.date >= :start')
                ->setParameter('vehicle', $vehicle)
                ->setParameter('start', $start)
                ->addOrderBy('e.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult()
            ;

            array_push($result, [
                'event' => $event,
                'checkpoints' => $checkpoints,
            ]);
        }

        return $this->response(200, $result);
    }

    /**
     * @Route(
     *     "/report_occurrence",
     *     name="report_occurrence",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *         "_format"="json",
     *     }
     * )
     */
    public function reportOccurrenceAction(
        OccurrenceSearchTypeFactory $formFactory
    ): JsonResponse {
        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.description = :type')
            ->setParameter('type', 'Ocorrência')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.modality = :modality')
            ->setParameter('modality', $modality)
            ->addOrderBy('e.id', 'DESC')
        ;

        if (is_object($this->getUser()->getCompany())) {
            $company = $this->getUser()->getCompany();
            $qb
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'e.vehicle = vehicle.id')
            ->andWhere('vehicle.company = :company')
            ->setParameter('company', $company)
            ;
        }

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

        $qbOccurrences = $qb->getQuery()->getResult();
        $qtdOccurrences = count($qbOccurrences);
        $result = [];
        foreach ($qbOccurrences as $key => $qbOccurrence) {
            if (!isset($result[$qbOccurrence->getCategory()->getDescription()])) {
                $result += [
                    $qbOccurrence->getCategory()->getDescription() => [
                            'qtd' => 1,
                            'percent' => 0,
                            'Ignorado' => 0,
                            'Visualizado' => 0,
                            'Resolvido' => 0,
                            'Em aberto' => 0,
                        ],
                    ];

                $result[
                    $qbOccurrence->getCategory()->getDescription()]['percent'] = number_format(($result[
                        $qbOccurrence->getCategory()->getDescription()]['qtd'] / $qtdOccurrences) * 100, 2);

                ++$result[
                    $qbOccurrence->getCategory()->getDescription()][$qbOccurrence->getStatus()->getDescription()];
            } else {
                ++$result[$qbOccurrence->getCategory()->getDescription()]['qtd'];

                $result[
                    $qbOccurrence->getCategory()->getDescription()]['percent'] = number_format(($result[
                        $qbOccurrence->getCategory()->getDescription()]['qtd'] / $qtdOccurrences) * 100, 2);

                ++$result[
                    $qbOccurrence->getCategory()->getDescription()][$qbOccurrence->getStatus()->getDescription()];
            }
        }

        // exportação em csv
        $now = new \DateTime();

        $occurrencesCsv = fopen('./assets/csv/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.csv', 'w');

        fputcsv($occurrencesCsv, ['Relatório geral de ocorrências']);
        fputcsv($occurrencesCsv, ['Tipo', 'Quantidade', 'Porcentagem de ocorrência', 'Em aberto', 'Visualizada', 'Resolvido', 'Ignorado']);
        foreach ($result as $key => $occurrence) {
            fputcsv($occurrencesCsv, [
                $key,
                $occurrence['qtd'],
                $occurrence['percent'],
                $occurrence['Em aberto'],
                $occurrence['Visualizado'],
                $occurrence['Resolvido'],
                $occurrence['Ignorado'],
            ]);
        }

        fclose($occurrencesCsv);

        // local
        // $entityCSV = FileHandler::recordFileBD('./assets/csv/resumo_das_ocorrencias-'.$now->format('d-m-Y H:i:s').'.csv');
        // servidor
        $entityCSV = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/csv/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.csv');
        if (is_object($this->getUser()->getCompany())) {
            $entityCSV->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityCSV);

        // exportação em Excel

        // include("PHPExcel/Classes/PHPExcel/IOFactory.php");
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setDelimiter(','); // define que a separação dos dados é feita por ponto e vírgula
        $objReader->setInputEncoding('UTF-8'); // habilita os caracteres latinos.
        $objPHPExcel = $objReader->load('./assets/csv/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.csv'); // indica qual o arquivo CSV que será convertido
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('./assets/excel/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.xls'); // Resultado da conversão; um arquivo do EXCEL

        // local
        // $entityXLS = FileHandler::recordFileBD('./assets/excel/resumo_das_ocorrencias-'.$now->format('d-m-Y H:i:s').'.xls');
        // servidor
        $entityXLS = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/excel/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.xls');
        if (is_object($this->getUser()->getCompany())) {
            $entityXLS->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityXLS);

        // exportação em pdf
        $html = '';

        $html .= '<h1>Relatório geral de ocorrências</h1><br>';
        $html .= '<table border=' . 1 . '>';
        $html .= '<tr>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Porcentagem de ocorrências</th>
                    <th>Em aberto</th>
                    <th>Visualizada</th>
                    <th>Resolvido</th>
                    <th>Ignorado</th>
                </tr>';
        foreach ($result as $key => $item) {
            $html .= '<tr>
                        <th>' . $key . '</th>
                        <th>' . $item['qtd'] . '</th>
                        <th>' . $item['percent'] . '</th>
                        <th>' . $item['Em aberto'] . '</th>
                        <th>' . $item['Visualizado'] . '</th>
                        <th>' . $item['Resolvido'] . '</th>
                        <th>' . $item['Ignorado'] . '</th>
                    </tr>';
        }
        $html .= '</table>';

        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($html);
        // local
        // $html2pdf->output('/var/www/html/painel.buslab.com.br-sistema-monolith/public/assets/pdf/resumo_das_ocorrencias-'.$now->format('d-m-Y H:i:s').'.pdf', 'F'); //Generate and load the PDF in the browser.
        // servidor
        $html2pdf->output('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/resumo_das_ocorrencias' . $now->format('d-m-Y H:i:s') . '.pdf', 'F'); // Generate and load the PDF in the browser.

        // local
        // $entityPDF = FileHandler::recordFileBD('./assets/pdf/resumo_das_ocorrencias-'.$now->format('d-m-Y H:i:s').'.pdf');
        // servidor
        $entityPDF = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.pdf');
        if (is_object($this->getUser()->getCompany())) {
            $entityPDF->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityPDF);

        $result = [
            'csv' => './assets/csv/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.csv',
            'xls' => './assets/excel/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.xls',
            'pdf' => './assets/pdf/resumo_das_ocorrencias-' . $now->format('d-m-Y H:i:s') . '.pdf',
        ]
        ;

        return $this->response(200, $result);
    }
}
