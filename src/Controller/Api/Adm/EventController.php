<?php

namespace App\Controller\Api\Adm;

use App\Entity\Event;
use App\Entity\EventModality;
use App\Entity\Trip;
use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use App\Form\Api\Adm\EventEditTypeFactory;
use App\Form\Api\Adm\EventSearchTypeFactory;
use App\Form\Api\Adm\EventTypeFactory;
use App\Form\Api\Adm\ObdSearchTypeFactory;
use App\Topnode\BaseBundle\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use PHPExcel;
use PHPExcel_IOFactory;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * @Route(
 *     "/api/adm/event",
 *     name="api_adm_event_"
 * )
 */
class EventController extends AbstractApiController
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
        EventSearchTypeFactory $formFactory
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
            ->leftJoin('e.category', 'eventType')
            ->leftJoin('e.sector', 'sector')
            ->leftJoin('e.status', 'eventStatus')
            ->leftJoin('e.driver', 'collaborators')
            ->setParameter('modality', EventModality::EVENT)
            ->addOrderBy('e.id', 'DESC')
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

            if (count($searchData['vehicle']) > 0) {
                $qb
                    ->andWhere('e.vehicle in (:vehicles)')
                    ->setParameter('vehicles', $searchData['vehicle'])
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
            ->andWhere('e.id = 2')
            ->getQuery()
            ->getOneOrNullResult()
        ;
        $entity = $entity->setModality($modality);

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

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/remove-cascade",
     *     name="remove",
     *     format="json",
     *     methods={"DELETE"},
     *     requirements={
     *         "_format"="json"
     *     }
     * )
     */
    public function removeCascadeAction(Event $entity): JsonResponse
    {
        $this->persist($entity->setIsActive(false));

        return $this->emptyResponse();
    }

    /**
     * @Route(
     *     "/{identifier}/list-events-by-trip",
     *     name="list_events_by_trip",
     *     format="json",
     *     methods={"GET"},
     *     requirements={
                "identifier"="[\w\-\_]{15}",
     *          "_format"="json"
     *     }
     * )
     */
    public function listEventsByTripAction(
        Trip $trip
    ): JsonResponse {
        $qb = $this->getEntityManager()
            ->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->andWhere('e.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('e.start', 'ASC')
        ;

        return $this->response(200, $this->paginate($qb));
    }

    /**
     * @Route(
     *     "/event_statistic",
     *     name="event_statistic",
     *     format="json",
     *     methods={"POST"},
     *     requirements={
     *          "_format"="json"
     *     }
     * )
     */
    public function eventStatisticAction(
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

        $modality = $this->getEntityManager()
            ->getRepository(EventModality::class)
            ->createQueryBuilder('e')
            ->andWhere('e.description = :description')
            ->setParameter('description', 'Evento')
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

        $events = $qb->getQuery()->getResult();
        $qtdEvents = count($events);
        $result = [];
        foreach ($events as $key => $event) {
            if (!isset($result[$event->getCategory()->getDescription()])) {
                $result += [$event->getCategory()->getDescription() => ['qtd' => 1]];
            } else {
                ++$result[$event->getCategory()->getDescription()]['qtd'];
            }
        }

        foreach ($result as $key => $item) {
            $result[$key] += ['percent' => number_format(($item['qtd'] / $qtdEvents) * 100, 2)];
        }

        // exportação em csv
        $now = new \DateTime();

        $eventsCsv = fopen('./assets/csv/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.csv', 'w');

        fputcsv($eventsCsv, ['Estatística de alertas']);
        fputcsv($eventsCsv, ['Tipo', 'Quantidade', 'Porcentagem de ocorrência']);
        foreach ($result as $key => $occurrence) {
            fputcsv($eventsCsv, [
                $key,
                $occurrence['qtd'],
                $occurrence['percent'],
            ]);
        }

        fclose($eventsCsv);

        // local
        // $entityCSV = FileHandler::recordFileBD('./assets/csv/estatistica_de_eventos-'.$now->format('d-m-Y H:i:s').'.csv');
        // servidor
        $entityCSV = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/csv/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.csv');
        if (is_object($this->getUser()->getCompany())) {
            $entityCSV->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityCSV);

        // exportação em Excel

        // include("PHPExcel/Classes/PHPExcel/IOFactory.php");
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setDelimiter(','); // define que a separação dos dados é feita por ponto e vírgula
        $objReader->setInputEncoding('UTF-8'); // habilita os caracteres latinos.
        $objPHPExcel = $objReader->load('./assets/csv/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.csv'); // indica qual o arquivo CSV que será convertido
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('./assets/excel/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.xls'); // Resultado da conversão; um arquivo do EXCEL

        // local
        // $entityXLS = FileHandler::recordFileBD('./assets/excel/estatistica_de_eventos-'.$now->format('d-m-Y H:i:s').'.xls');
        // servidor
        $entityXLS = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/excel/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.xls');
        if (is_object($this->getUser()->getCompany())) {
            $entityXLS->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityXLS);

        // exportação em pdf
        $html = '';

        $html .= '<h1>Estatística de alertas</h1><br>';
        $html .= '<table border=' . 1 . '>';
        $html .= '<tr>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Porcentagem de ocorrências</th>
                </tr>';
        foreach ($result as $key => $item) {
            $html .= '<tr>
                        <th>' . $key . '</th>
                        <th>' . $item['qtd'] . '</th>
                        <th>' . $item['percent'] . '</th>
                    </tr>';
        }
        $html .= '</table>';

        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($html);
        // local
        // $html2pdf->output('/var/www/html/painel.buslab.com.br-sistema-monolith/public/assets/pdf/estatistica_de_eventos-'.$now->format('d-m-Y H:i:s').'.pdf', 'F'); //Generate and load the PDF in the browser.
        // servidor
        $html2pdf->output('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/estatistica_de_eventos' . $now->format('d-m-Y H:i:s') . '.pdf', 'F'); // Generate and load the PDF in the browser.

        // local
        // $entityPDF = FileHandler::recordFileBD('./assets/pdf/estatistica_de_eventos-'.$now->format('d-m-Y H:i:s').'.pdf');
        // servidor
        $entityPDF = FileHandler::recordFileBD('/var/www/api.buslab.com.br/httpdocs/public/assets/pdf/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.pdf');
        if (is_object($this->getUser()->getCompany())) {
            $entityPDF->setCompany($this->getUser()->getCompany());
        }

        $this->persist($entityPDF);

        $result = [
            'csv' => './assets/csv/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.csv',
            'xls' => './assets/excel/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.xls',
            'pdf' => './assets/pdf/estatistica_de_eventos-' . $now->format('d-m-Y H:i:s') . '.pdf',
        ]
        ;

        return $this->response(200, $result);
    }
}
