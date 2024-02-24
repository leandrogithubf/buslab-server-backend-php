<?php

namespace App\Topnode\BaseBundle\Controller;

use App\Topnode\BaseBundle\Utils\Event\ImageEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract class to allow less code and better code reuse at the systema on
 * handling entity datas in CRUD.
 */
abstract class AbstractCrudController extends Controller
{
    /**
     * Identifies a many-to-one association with doctrine.
     */
    private const MANY_TO_ONE = 2;

    /**
     * Identifies a one-to-many association with doctrine.
     */
    private const ONE_TO_MANY = 4;

    /**
     * Identifies a many-to-many association with doctrine.
     */
    private const MANY_TO_MANY = 8;

    /**
     * The complete namespace of the entity class.
     *
     * @var string
     */
    protected $entityClass;

    /**
     * The complete namespace of the entity form for handling data.
     *
     * @var string
     */
    protected $formClass;

    /**
     * The complete namespace of the entity form for filtering data.
     *
     * @var string
     */
    protected $formFilterClass;

    /**
     * The path to the form template file to be rendered.
     *
     * @var string
     */
    protected $formTemplate = '@TopnodeBase/crud/partials/form_modal.html.twig';

    /**
     * The path to the table entity template.
     *
     * @var string
     */
    protected $tableEntityTemplate = '@TopnodeBase/macros/table.entity.html.twig';

    /**
     * The path to the list row template file to be rendered.
     *
     * @var string
     */
    protected $listRowTemplate = '@TopnodeBase/crud/partials/list_row.html.twig';

    /**
     * The path to the list row reactivation template file to be rendered.
     *
     * @var string
     */
    protected $reactivateRowTemplate = '@TopnodeBase/crud/partials/list_reactivate.html.twig';

    /**
     * The path to the list row removed template file to be rendered.
     *
     * @var string
     */
    protected $removedRowTemplate = '@TopnodeBase/crud/partials/list_removed.html.twig';

    /**
     * The path to the list template file to be rendered.
     *
     * @var string
     */
    protected $listTemplate = '@TopnodeBase/crud/list.html.twig';

    /**
     * The path to confirm cascade template.
     *
     * @var string
     */
    protected $deleteOperationsConfirmTemplate = '@TopnodeBase/crud/deletion/confirmDeleteOperations.html.twig';

    /**
     * The flag to decide if show action will return a
     * modal or redirect to other page.
     *
     * @var bool
     */
    protected $isShowInModal = true;

    /**
     * The path to show modal template.
     *
     * @var string
     */
    protected $showModalTemplate = '@TopnodeBase/crud/showModal.html.twig';

    /**
     * The path to show template.
     *
     * @var string
     */
    protected $showTemplate = '@TopnodeBase/crud/show.html.twig';

    /**
     * The path to show content template.
     *
     * @var string
     */
    protected $showTemplateContent = null;

    /**
     * The string to be prepended on the translations strings. The domain of the
     * translations for this entity in this controller.
     *
     * @var string
     */
    protected $entityTranslationDomain;

    /**
     * List of fields to be searched with the "search" filter in form. Can be
     * replaced or added as needed by any data. Only uses the ones in the
     * intersection with this fields and the metadata.
     *
     * @var string[]
     */
    protected $defaultSearchFields = ['name', 'description'];

    /**
     * Path to load form scripts.
     *
     * @var string
     */
    protected $formScripts;

    /**
     * indentifier of exclusion method.
     *
     * @var string
     */
    protected $methodOfExclusion = 'deactivate';

    /**
     * List with relations to cascade delete
     * on remove.
     *
     * @var string[]
     */
    protected $cascadeOnDelete = [];

    /**
     * List with relations to set null on remove.
     *
     * @var string[]
     */
    protected $setNullOnDelete = [];

    /**
     * List with relations to potect on remove.
     *
     * @var string[]
     */
    protected $protectOnDelete = [];

    /**
     * List with relations to set another
     * register on delete.
     *
     * @var string[]
     */
    protected $setOnDelete = [];

    /**
     * List with search terms for the set on delete method.
     *
     * @var string[]
     */
    protected $seachTermSetOnDelete = null;

    /**
     * Constructor class for AbstractCrudController. Calls the method
     * responsible to check if all the information needed is configured.
     */
    public function __construct()
    {
        $this->validate();
    }

    /**
     * Renders a paginated list of the current entity.
     *
     * @Route(
     *     "/list",
     *     name="list",
     *     methods="GET"
     * )
     */
    public function listAction(Request $request): Response
    {
        $qb = $this->getRepository($this->entityClass)->createQueryBuilder('e');
        $qb = $this->applyFilterJoins($qb, $request);

        $formFilter = $this->createForm($this->formFilterClass);
        $qb = $this->applyFilterForm($qb, $formFilter, $request);

        $list = $this
            ->get('tn.utils.paginator.view')
            ->paginate($qb->getQuery())
        ;

        $format = $request->get('_format', 'html');
        if ('json' == $format) {
            return $this
                ->get('tn.utils.api.response')
                ->response(200, $list)
            ;
        }

        return $this->render($this->listTemplate, [
            'list' => $list,
            'form_filter' => $formFilter->createView(),
            'isShowInModal' => $this->isShowInModal,
            'tableEntityTemplate' => $this->tableEntityTemplate,
        ]);
    }

    /**
     * Deletes the current entity with a funcion call to deactivate.
     *
     * @Route(
     *     "/delete/{id}",
     *     name="delete",
     *     methods="DELETE",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function deleteAction(Request $request, $id = 0): JsonResponse
    {
        $em = $this->getEntityManager();
        $entity = $this->findEntity(['id' => $id]);

        // Checks if the entity is valid
        if (!$entity instanceof $this->entityClass) {
            return $this->get('tn.utils.api.response')
                ->error(404, $this->entityTranslationDomain . 'delete.error')
            ;
        }

        // Checks if the entity is allowed to be deleted
        if (!$this->isAllowedDelete($entity)) {
            return $this->get('tn.utils.api.response')
                ->error(401, $this->entityTranslationDomain . 'delete.allow.error')
            ;
        }

        // Checks if the entity is able to be deleted regarding it's relations
        $deletionStatus = 'success';

        try {
            $confirmed = $request->get('confirmed', false);
            $setNewId = $request->get('setNewId', null);

            if ('false' === $confirmed) {
                $confirmed = false;
            }

            if ('true' === $confirmed) {
                $confirmed = true;
            }

            $handleRelationsMethods = $this->handleRelationsDeleteMethods($em, $entity);
            $deletionStatus = $this->handleRelationsOnDelete($handleRelationsMethods, $confirmed, $entity, $em, $setNewId);
        } catch (\Exception $e) {
            return $this->get('tn.utils.api.response')
                ->error(401, $e->getMessage())
            ;
        }
        // Default information to be returned to the user
        $message = 'delete.success';
        $isLock = false;

        $row = $this->renderView($this->removedRowTemplate, [
            'message' => $this->entityTranslationDomain . 'delete.confirm',
            'entity' => $entity,
        ]);

        if ('deactivate' == $this->methodOfExclusion && 'success' == $deletionStatus) {
            $this->deactivate($em, $entity);

            $row = $this->renderView($this->reactivateRowTemplate, [
                'message_reactivate' => $this->entityTranslationDomain . 'delete.success_reactivate',
                'message_deleted' => $this->entityTranslationDomain . 'delete.success',
                'entity' => $entity,
            ]);
        } elseif ('lock' == $this->methodOfExclusion) {
            if ($entity->getIsLocked()) {
                $this->deactivate($em, $entity);

                $row = $this->renderView($this->reactivateRowTemplate, [
                    'message_reactivate' => $this->entityTranslationDomain . 'delete.success_reactivate',
                    'message_deleted' => $this->entityTranslationDomain . 'delete.success',
                    'entity' => $entity,
                ]);
            } else {
                $message = 'delete.success_lock';
                $isLock = true;

                $this->lock($em, $entity);

                $row = $this->renderView($this->listRowTemplate, [
                    'entity' => $entity,
                    'message' => $this->entityTranslationDomain . 'delete.success_lock',
                    'tableEntityTemplate' => $this->tableEntityTemplate,
                ]);
            }
        } elseif ('remove' == $this->methodOfExclusion && 'success' == $deletionStatus) {
            $this->remove($em, $entity);

            $row = $this->renderView($this->removedRowTemplate, [
                'message' => $this->entityTranslationDomain . 'delete.success',
                'entity' => $entity,
            ]);
        }

        if ('success' !== $deletionStatus) {
            if (count($deletionStatus) > 0) {
                $form = null;
                foreach ($deletionStatus as $key => $value) {
                    if ('set' === $value) {
                        $form = $this->generateSetOnDeleteForm($entity, $key);
                    }
                }

                $modal = $this->renderView($this->deleteOperationsConfirmTemplate, [
                    'entityTranslationDomain' => $this->entityTranslationDomain,
                    'deletions' => $deletionStatus,
                    'form' => ($form ? $form->createView() : ''),
                ]);

                return $this->get('tn.utils.api.response')->response(300, [
                    'is_lock' => $isLock,
                    'entity' => $entity,
                    'modal' => $modal,
                    'message' => $this->entityTranslationDomain,
                ]);
            }
        }

        return $this->get('tn.utils.api.response')->response(200, [
            'is_lock' => $isLock,
            'entity' => $entity,
            'message' => $this->entityTranslationDomain . $message,
            'deletionStatus' => $deletionStatus,
            'row' => $row,
        ]);
    }

    /**
     * Reactivates the current entity with a funcion call to activate.
     *
     * @Route(
     *     "/reactivate/{id}",
     *     name="reactivate",
     *     methods="POST",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function reactivateAction(Request $request, $id = 0): JsonResponse
    {
        $em = $this->getEntityManager();
        $entity = $this->findEntity(['id' => $id]);

        if (!$entity instanceof $this->entityClass) {
            return $this->get('tn.utils.api.response')
                ->error(404, $this->entityTranslationDomain . 'reactivate.error')
            ;
        }

        if (!$this->get('tn.utils.entity.decisor.reactivate')->isAllowed($entity)) {
            return $this->get('tn.utils.api.response')
                ->error(404, $this->entityTranslationDomain . 'reactivate.error')
            ;
        }

        if ('deactivate' == $this->methodOfExclusion) {
            $this->activate($em, $entity);
        } elseif ('lock' == $this->methodOfExclusion) {
            if ($entity->getIsActive()) {
                $this->unLock($em, $entity);
            } else {
                $this->activate($em, $entity);
            }
        }

        return $this->get('tn.utils.api.response')->response(200, [
            'entity' => $entity,
            'message' => $this->entityTranslationDomain . 'reactivate.success',
            'row' => $this->renderView($this->listRowTemplate, [
                'entity' => $entity,
                'tableEntityTemplate' => $this->tableEntityTemplate,
            ]),
        ]);
    }

    /**
     * Creates a new entity with a funcion call to persist.
     *
     * @Route(
     *     "/new",
     *     name="new",
     *     methods="POST"
     * )
     */
    public function newAction(Request $request): JsonResponse
    {
        $em = $this->getEntityManager();
        $entity = $this->newEntity($em);

        return $this->storeNewEntity(
            $request,
            $entity,
            $this->entityTranslationDomain . 'form.success_new',
            $this->getNewParameters($entity)
        );
    }

    /**
     * Duplicates an existing entity into the databae  with a funcion call to
     * persist.
     *
     * @Route(
     *     "/duplicate/{id}",
     *     name="duplicate",
     *     methods="POST",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function duplicateAction(Request $request, $id): JsonResponse
    {
        $entity = $this->clone($this->findEntity(['id' => $id]));

        if (!$entity instanceof $this->entityClass) {
            return $this->get('tn.utils.api.response')->error(404, $this->entityTranslationDomain . 'form.not_found');
        }

        return $this->storeNewEntity(
            $request,
            $entity,
            $this->entityTranslationDomain . 'form.success_duplicate',
            $this->getDuplicateParameters($entity)
        );
    }

    /**
     * Updates an existing entity with a funcion call to persist.
     *
     * @Route(
     *     "/edit/{id}",
     *     name="edit",
     *     methods="POST|PUT",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function editAction(Request $request, $id): JsonResponse
    {
        $em = $this->getEntityManager();
        $entity = $this->findEntity(['id' => $id]);

        if (!$entity instanceof $this->entityClass) {
            return $this->get('tn.utils.api.response')->error(404, $this->entityTranslationDomain . 'form.not_found');
        }

        if (!$this->isAllowedEdit($entity)) {
            return $this->get('tn.utils.api.response')
                ->error(401, $this->entityTranslationDomain . 'edit.allow.error')
            ;
        }

        $form = $this->generateEntityForm($entity);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->get('tn.utils.api.response')->error(400, $this->entityTranslationDomain . 'form.form_error');
        }

        if (!$form->isValid()) {
            return $this->get('tn.utils.api.response')->errorFromForm($form->getErrors(true), $this->entityTranslationDomain . 'form.validation_error');
        }

        $this->persist($em, $entity, []);
        $em->flush();

        $parameters = [
            'entity' => $entity,
            'is_new' => false,
            'row' => $this->renderView($this->listRowTemplate, [
                'entity' => $entity,
                'tableEntityTemplate' => $this->tableEntityTemplate,
            ]),
            'message' => $this->entityTranslationDomain . 'form.success_edit',
        ];

        $extraParameters = $this->getEditParameters($entity);
        if (count($extraParameters) > 0) {
            $parameters = array_merge($parameters, $extraParameters);
        }

        return $this->get('tn.utils.api.response')->response(200, $parameters);
    }

    /**
     * Show entity modal.
     *
     * @Route(
     *     "/show/{id}",
     *     name="show",
     *     methods="GET",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function showAction(Request $request, $id)
    {
        $entity = $this->findEntity(['id' => $id]);

        if (!$entity instanceof $this->entityClass) {
            return $this->get('tn.utils.api.response')->error(404, $this->entityTranslationDomain . 'entity.not_found');
        }

        if (!$this->isAllowedView($entity)) {
            return $this->get('tn.utils.api.response')
                ->error(401, $this->entityTranslationDomain . 'show.allow.error')
            ;
        }

        if (null === $this->showTemplateContent) {
            return $this->get('tn.utils.api.response')->error(500, 'Show content not defined');
        }

        $parameters = [
            'entity' => $entity,
        ];

        $extraParameters = $this->getShowParameters($entity);
        if (count($extraParameters) > 0) {
            $parameters = array_merge($parameters, $extraParameters);
        }

        $content = $this->renderView($this->showTemplateContent, [
            'entity' => $entity,
        ]);

        if ($this->isShowInModal) {
            $modal = $this->renderView($this->showModalTemplate, [
                'content' => $content,
                'translationDomain' => $this->entityTranslationDomain,
            ]);

            return $this->get('tn.utils.api.response')->response(200, $modal);
        }

        return $this->render($this->showTemplate, [
            'content' => $content,
        ]);
    }

    /**
     * From the list filter and sort information, generates and downloads an CSV.
     *
     * @Route(
     *     "/export",
     *     name="export",
     *     methods="GET"
     * )
     *
     * @todo This method and listAction shares the first lines and should be
     * generalized to remove code duplication.
     *
     * @param TranslatorInterface $request
     */
    public function exportAction(Request $request, TranslatorInterface $translator): Response
    {
        $qb = $this->getRepository($this->entityClass)->createQueryBuilder('e');
        $qb = $this->applyFilterJoins($qb, $request);

        $formFilter = $this->createForm($this->formFilterClass);
        $qb = $this->applyFilterForm($qb, $formFilter, $request);

        $data = $this->generateCSV(
            $this->generateExportTitles(),
            $this->generateExportData($qb->getQuery()->getResult())
        );

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-length', strlen($data));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $this->generateExportFileName($translator) . '";');
        $response->sendHeaders();

        $response->setContent($data);

        return $response;
    }

    /**
     * Generates a form to be rendered on screen to update or create an entity.
     *
     * @Route(
     *     "/form/generate/{id}",
     *     name="form_generate",
     *     methods="GET",
     *     defaults={"id": 0},
     *     requirements={"id"="\d+"}
     * )
     *
     * @param string|int $id The current entity ID to be selected from DB
     */
    public function formGeneratorAction(Request $request, $id = 0): Response
    {
        $entity = $this->findEntity(['id' => $id]);

        $actionRequest = $request->get('action');
        if (0 === strlen($actionRequest)) {
            if ($entity instanceof $this->entityClass) {
                $actionRequest = 'edit';
            } else {
                $actionRequest = 'new';
            }
        }

        if ('edit' == $actionRequest && !$this->isAllowedEdit($entity)) {
            return $this->get('tn.utils.api.response')
                ->error(401, $this->entityTranslationDomain . 'edit.allow.error')
            ;
        }

        if ('new' == $actionRequest && !$this->isAllowedCreate()) {
            return $this->get('tn.utils.api.response')
                ->error(401, $this->entityTranslationDomain . 'new.allow.error')
            ;
        }

        if (!$entity instanceof $this->entityClass) {
            $entity = new $this->entityClass();
        }

        $entity = $this->parseParametersForEntity($entity, $request);

        $title = $request->get('title', '');
        if (0 === strlen($title)) {
            $title = $this->entityTranslationDomain . 'form.title.' . $actionRequest;
        }

        $routeData = $this->get('tn.utils.generator.breadcrumb')->generateBreadcrumbData($request->get('_route'));

        $parameters = [];
        if ($entity->getId() > 0) {
            $parameters['id'] = $entity->getId();
        }

        if (strlen($request->get('customAction')) > 0) {
            $action = $request->get('customAction');
        } else {
            $action = $this->generateUrl($routeData['crud'][$actionRequest], $parameters);
        }

        $method = 'POST';

        $form = $this->generateEntityForm($entity, [
            'action' => $action,
            'method' => $method,
        ]);

        $metadata = [];

        foreach ($form->getIterator() as $key => $value) {
            $type = $value->getConfig()->getType()->getInnerType();

            $metadata[$key]['field'] = $value->getConfig()->getType()->getInnerType()->getBlockPrefix();
            if ('Symfony\Component\Form\Extension\Core\Type\ChoiceType' == get_class($type)) {
                $metadata[$key]['options'] = $value->getConfig()->getOptions()['choices'];
            } elseif ('entity' == $type) {
                $metadata[$key]['options'] = 'route';
            }
        }

        $format = $request->get('_format', 'html');
        if ('json' == $format) {
            return $this->get('tn.utils.api.response')->response(200, $metadata);
        }

        return $this->render(
            $this->formTemplate,
            array_merge(
                $this->getFormParameters(),
                [
                    'title' => $title,
                    'entity' => $entity,
                    'form' => $form->createView(),
                ]
            )
        );
    }

    public function storeNewEntity(Request $request, object $entity, string $message, array $extraParameters = []): Response
    {
        $em = $this->getEntityManager();

        $form = $this->generateEntityForm($entity);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->get('tn.utils.api.response')->error(400, $this->entityTranslationDomain . 'form.form_error');
        }

        if (!$form->isValid()) {
            return $this->get('tn.utils.api.response')->errorFromForm($form->getErrors(true), $this->entityTranslationDomain . 'form.validation_error');
        }

        $formExtraData = [];
        foreach ($extraParameters as $parameter) {
            if (isset($form[$parameter])) {
                $formExtraData[$parameter] = $form[$parameter]->getData();
            }
        }

        $result = $this->persist($em, $entity, $formExtraData);
        if (is_string($result)) {
            return $this->get('tn.utils.api.response')->error(400, $result);
        } elseif (!$result) {
            return $this->get('tn.utils.api.response')->error(400, $this->entityTranslationDomain . 'form.generic_error');
        }

        $parameters = [
            'entity' => $entity,
            'is_new' => true,
            'row' => $this->renderView($this->listRowTemplate, [
                'entity' => $entity,
                'tableEntityTemplate' => $this->tableEntityTemplate,
            ]),
            'message' => $message,
        ];

        if (count($extraParameters) > 0) {
            $parameters = array_merge(
                $parameters,
                $extraParameters
            );
        }

        $parameters = array_merge(
            $parameters,
            $this->getExtraParameters($entity)
        );

        return $this->get('tn.utils.api.response')->response(200, $parameters);
    }

    /**
     * Allows the entity to receive information from the parameters as needed.
     */
    public function parseParametersForEntity(object $entity, Request $request): object
    {
        return $entity;
    }

    /**
     *processes the delete settings and returns the available options
     *for a list of entities.
     *
     * @param object                 $entity
     * @param EntityManagerInterface $em
     */
    public function handleRelationsDeleteMethods($em, $entity, ?string $class = null, ?string $methodOfExclusion = null): array
    {
        if (!$class) {
            $class = $this->entityClass;
        }

        if (!$methodOfExclusion) {
            $methodOfExclusion = $this->methodOfExclusion;
        }

        $relations = $em->getClassMetadata($class)->getAssociationMappings();
        $handleMethods = [];

        if ('lock' == $methodOfExclusion) {
            if (!$entity->getIsLocked()) {
                $handleMethods = [];
            }
        } else {
            foreach ($relations as $key => $relation) {
                $getFunction = $this->generateGetFunction($key);
                $notHasRelations = false;

                if (is_array($entity->{$getFunction}()) || $entity->{$getFunction}() instanceof Countable ||
                $entity->{$getFunction}() instanceof PersistentCollection) {
                    if (0 == count($entity->{$getFunction}())) {
                        $notHasRelations = true;
                    }
                } else {
                    if (is_null($entity->{$getFunction}())) {
                        $notHasRelations = true;
                    }
                }

                if ($notHasRelations) {
                    continue;
                }

                if (in_array($key, $this->cascadeOnDelete)) {
                    $handleMethods[$key] = 'cascade';
                }

                if (in_array($key, $this->setNullOnDelete)) {
                    $handleMethods[$key] = 'setNull';
                }

                if (in_array($key, $this->protectOnDelete)) {
                    $handleMethods[$key] = 'protect';
                }

                if (in_array($key, $this->setOnDelete)) {
                    $handleMethods[$key] = 'set';

                    if (null === $this->seachTermSetOnDelete) {
                        throw new \Exception('necessario informar o termo de busca para esse tipo de deleção');
                    }
                }
            }
        }

        return [
            'methods' => $handleMethods,
        ];
    }

    /**
     * calls the right function based on the user's choice.
     *
     * @param $methods
     */
    public function handleRelationsOnDelete($methods, $isComfirmed, object $entity, EntityManagerInterface $em, $setNewId)
    {
        if (!isset($methods['methods'])) {
            return 'success';
        }

        $isSucess = true;
        $return = [];

        foreach ($methods['methods'] as $key => $method) {
            switch ($method) {
                case 'setNull':
                    if ($isComfirmed) {
                        $this->handleSetNullOnDelete($entity, $key, $em);
                    } else {
                        $isSucess = false;
                        $return[$key] = 'setNull';
                    }
                    break;

                case 'set':
                    if ($isComfirmed) {
                        $this->handleSetOnDelete($entity, $key, $em, $setNewId);
                    } else {
                        $isSucess = false;
                        $return[$key] = 'set';
                    }
                    break;

                case 'protect':
                    try {
                        $this->handleProtectOnDelete($entity, $key, $em);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                    break;

                case 'cascade':
                    if ($isComfirmed) {
                        $this->handleCascadeOnDelete($entity, $key, $em);
                    } else {
                        $isSucess = false;
                        $return[$key] = 'cascade';
                    }
                    break;
            }
        }

        if ($isSucess) {
            return 'success';
        }

        return $return;
    }

    /**
     *enter null in the fields of the deleted entities.
     *
     * @param object $entity
     * @param $relationField
     */
    public function handleSetNullOnDelete($entity, $relationField, EntityManagerInterface $em)
    {
        $relationMetadata = $em->getClassMetadata($this->entityClass)
            ->getAssociationMappings($relationField)[$relationField]
        ;

        if (AbstractCrudController::ONE_TO_MANY == $relationMetadata['type']) {
            $qb = $em->createQueryBuilder('e')
                ->update($relationMetadata['targetEntity'], 'e')
                ->set('e.' . $relationMetadata['mappedBy'], ':setOther')
                ->where('e.' . $relationMetadata['mappedBy'] . ' = :deletingEntity')
                ->setParameter('deletingEntity', $entity)
                ->setParameter('setOther', null)
            ;

            $qb->getQuery();
            $qb->getQuery()->execute();
        } elseif (AbstractCrudController::MANY_TO_ONE == $relationMetadata['type']) {
            $getFunction = $this->generateGetFunction($relationField);
            $setFunction = preg_replace('/get/', 'set', $getFunction, 1);
            $entity->{$setFunction}(null);

            $em->persist($entity);
            $em->flush();
        } elseif (AbstractCrudController::MANY_TO_MANY == $relationMetadata['type']) {
            $getFunction = 'get' . ucfirst($relationField);

            $relations = $entity->{$getFunction}();

            $removeString = substr($relationField, 0, -1);
            $removeFunction = 'remove' . ucfirst($removeString);

            foreach ($relations as $relation) {
                $entity->{$removeFunction}($relation);
            }

            $em->persist($entity);
            $em->flush();
        }
    }

    /**
     * Will relate all items of risk to another element chosen by the user.
     *
     * @param $relationField
     * @param mixed $idSetEntity
     *
     * @todo typehint parameters
     */
    public function handleSetOnDelete(object $entity, $relationField, EntityManagerInterface $em, $idSetEntity)
    {
        $setEntity = $this->findEntity(['id' => $idSetEntity]);
        $relationMetadata = $em->getClassMetadata($this->entityClass)
            ->getAssociationMappings($relationField)[$relationField]
        ;

        if (AbstractCrudController::MANY_TO_ONE == $relationMetadata['type'] ||
            AbstractCrudController::ONE_TO_MANY == $relationMetadata['type']) {
            $qb = $em->createQueryBuilder('e')
                ->update($relationMetadata['targetEntity'], 'e')
                ->set('e.' . $relationMetadata['mappedBy'], ':setOther')
                ->where('e.' . $relationMetadata['mappedBy'] . ' = :deletingEntity')
                ->setParameter('deletingEntity', $entity)
                ->setParameter('setOther', $setEntity)
            ;

            $qb->getQuery()->getResult();
        } elseif (AbstractCrudController::MANY_TO_MANY == $relationMetadata['type']) {
            $getFunction = 'get' . ucfirst($relationField);

            $relations = $entity->{$getFunction}();

            $relationString = substr($relationField, 0, -1);
            $removeFunction = 'remove' . ucfirst($relationString);
            $AddFunction = 'remove' . ucfirst($relationString);

            foreach ($relations as $relation) {
                $entity->{$removeFunction}($relation);
                $setEntity->{$AddFunction}($relation);
            }

            $em->persist($entity);
            $em->persist($setEntity);
            $em->flush();
        }
    }

    /**
     * Generate form for user select a new element to intens of risk relate.
     *
     * @param $relationField
     */
    public function generateSetOnDeleteForm(object $entity, $relationField)
    {
        return $this->createFormBuilder()
            ->add($relationField, EntityType::class, [
                'class' => $this->entityClass,
                'label' => false,
                'choice_label' => $this->seachTermSetOnDelete,
                'attr' => [
                    'class' => 'set-new-select',
                ],
                'query_builder' => function (EntityRepository $er) use ($entity) {
                    return $er->createQueryBuilder('e')
                        ->where('e.id != :entity')
                        ->setParameter('entity', $entity)
                    ;
                },
            ])
            ->getForm()
        ;
    }

    /**
     *does the protection against deletion in case there are external fields
     *attached to the register.
     *
     * @param object $entity
     * @param $relationField
     */
    public function handleProtectOnDelete($entity, $relationField, EntityManagerInterface $em)
    {
        $getFunction = 'get' . ucfirst($relationField);

        if (is_array($entity->{$getFunction}()) || $entity->{$getFunction}() instanceof Countable ||
                $entity->{$getFunction}() instanceof PersistentCollection) {
            if (count($entity->{$getFunction}()) > 0) {
                throw new \Exception('Não é possivel excluir por existir ' . lcfirst($this->translateElement($relationField)) . ' atrelado(as) a este cadastro');
            }
        } else {
            if (!is_null($entity->{$getFunction}())) {
                throw new \Exception('Não é possivel excluir por existir ' . lcfirst($this->translateElement($relationField)) . ' atrelado(as) a este cadastro');
            }
        }
    }

    /**
     *deletes cascading.
     */
    public function handleCascadeOnDelete(object $entity, string $relationField, EntityManagerInterface $em)
    {
        $getFunction = 'get' . ucfirst($relationField);
        $relations = $entity->{$getFunction}();

        foreach ($relations as $relation) {
            $this->deactivate($em, $relation);
        }
    }

    /**
     * To do.
     */
    public function isAllowedDelete(object $entity): bool
    {
        return true;
    }

    /**
     * To do.
     */
    public function isAllowedEdit(object $entity): bool
    {
        return true;
    }

    /**
     * To do.
     */
    public function isAllowedView(object $entity): bool
    {
        return true;
    }

    /**
     * To do.
     */
    public function isAllowedCreate(): bool
    {
        return true;
    }

    /**
     *The function does the translation of parameter.
     *
     * @param $element
     *
     * @return $element translated
     */
    protected function translateElement($element)
    {
        $domain = $this->entityTranslationDomain . 'form.fields.' . $element;

        return $this->get('translator')->trans($domain);
    }

    /**
     * Creates a get function.
     */
    protected function generateGetFunction(string $field): string
    {
        $exploded = explode('_', $field);
        $return = 'get';
        foreach ($exploded as $value) {
            $return .= ucfirst($value);
        }

        return $return;
    }

    /**
     *.To do.
     */
    protected function getNewParameters(object $entity): array
    {
        return [];
    }

    /**
     *.To do.
     */
    protected function getEditParameters(object $entity): array
    {
        return [];
    }

    /**
     *.To do.
     */
    protected function getDuplicateParameters(object $entity): array
    {
        return [];
    }

    /**
     *.To do.
     */
    protected function getShowParameters(object $entity): array
    {
        return [];
    }

    /**
     * Allows to pass form configuration parameters without having to override
     * a full action ot method of this class.
     */
    protected function getFormParameters(): array
    {
        return [];
    }

    /**
     * Creates a new entity the type from the current CRUD.
     *
     * @return new object entity
     */
    protected function newEntity(EntityManagerInterface $em): object
    {
        return new $this->entityClass();
    }

    /**
     * Finds a entity of the type from the current CRUD and by the parameters.
     *
     * @return object entity
     */
    protected function findEntity(array $parameters): ?object
    {
        return $this->getRepository($this->entityClass)->find($parameters['id']);
    }

    /**
     * Clones an entity passed.
     *
     * @return object The cloned entity
     */
    protected function clone(object $entity): object
    {
        return clone $entity;
    }

    /**
     * Method to just handle the database persistence of the entity.
     */
    protected function persist(EntityManagerInterface $em, object $entity, array $extraFormData = [])
    {
        if ('lock' == $this->methodOfExclusion) {
            $entity->setIsLocked(false);
        }
        $imageEvent = new ImageEvent($entity, $em);
        $this->get('event_dispatcher')->dispatch('image.handle.event', $imageEvent);
        $em->persist($entity);
        $em->flush();

        return true;
    }

    /**
     * Method to just handle the database activation of the entity.
     */
    protected function activate(EntityManagerInterface $em, object $entity): void
    {
        $em->persist($entity->setIsActive(true));
        $em->flush();
    }

    /**
     * Method to just handle the database deactivation of the entity.
     */
    protected function deactivate(EntityManagerInterface $em, object $entity): void
    {
        $em->persist($entity->setIsActive(false));
        $em->flush();
    }

    /**
     * Method to just handle the database removal of the entity.
     */
    protected function remove(EntityManagerInterface $em, object $entity): void
    {
        $em->remove($entity);
        $em->flush();
    }

    /**
     * Method to just handle the database unlock of the entity.
     */
    protected function unLock(EntityManagerInterface $em, object $entity): void
    {
        $em->persist($entity->setIsLocked(false));
        $em->flush();
    }

    /**
     * Method to just handle the database lock of the entity.
     */
    protected function lock(EntityManagerInterface $em, object $entity): void
    {
        $em->persist($entity->setIsLocked(true));
        $em->flush();
    }

    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->entityClass);
    }

    /**
     * The abstract function to be implemented to allow the user to implement
     * the best search for his problem.
     */
    protected function applyFilterForm(
        QueryBuilder $qb,
        FormInterface $form,
        Request $request
    ): QueryBuilder {
        $data = $this->getFilterData($qb, $form, $request);
        $qb = $this->applyCustomFilters($qb, $form, $request, $data);
        if (0 == count($data)) {
            return $qb;
        }

        $em = $this->getEntityManager();
        $metadata = $em->getClassMetadata($this->entityClass);

        $searchable = [];
        foreach ($data as $field => $value) {
            if (empty($value)) {
                continue;
            }

            if ('search' == $field) {
                foreach ($this->defaultSearchFields as $searchField) {
                    if (array_key_exists($searchField, $metadata->fieldMappings)) {
                        $searchable[] = 'e.' . $searchField;
                    }
                }
                continue;
            }

            if (array_key_exists($field, $metadata->fieldMappings)) {
                $type = $metadata->fieldMappings[$field]['type'];
                if (in_array($type, ['string', 'text'])) {
                    $hasWhereAdded = false;
                    foreach ($qb->getParameters() as $parameter) {
                        if ($parameter->getName() == $field) {
                            $hasWhereAdded = true;
                            break;
                        }
                    }

                    if (!$hasWhereAdded) {
                        $qb
                            ->andWhere('e.' . $field . ' LIKE :' . $field)
                            ->setParameter($field, '%' . $value . '%')
                        ;
                    }

                    continue;
                }

                if (in_array($type, ['integer'])) {
                    $qb
                        ->andWhere('e.' . $field . ' = :' . $field)
                        ->setParameter($field, $value)
                    ;
                    continue;
                }

                // TODO: Filtrar campos que não são string ou números, como data e booleano
            } elseif (array_key_exists($field, $metadata->associationMappings)) {
                if ($value instanceof ArrayCollection) {
                    if (!in_array($field, $qb->getAllAliases())) {
                        $qb->leftJoin('e.' . $field, $field)
                            ->andWhere($field . ' IN (:' . $field . ')')
                            ->setParameter($field, $value)
                        ;
                    }
                    continue;
                }

                if (!in_array($field, $qb->getAllAliases())) {
                    $qb->leftJoin('e.' . $field, $field);
                }

                $qb
                    ->andWhere($field . ' = :' . $field)
                    ->setParameter($field, $value)
                ;

                continue;
            } else {
                continue;
            }

            $qb
                ->andWhere('e.' . $field . ' = :' . $field)
                ->setParameter($field, $value)
            ;
        }

        if (count($searchable) > 0) {
            $qb = $this->applyFilterByLikeness($qb, $data, $searchable);
        }

        return $qb;
    }

    /**
     * Allows user to implement custom filter for entity listing without losing
     * all the data handling already implemented on applyFilterForm.
     */
    protected function applyCustomFilters(
        QueryBuilder $qb,
        FormInterface $form,
        Request $request,
        array $data
    ): QueryBuilder {
        return $qb;
    }

    /**
     * Given a form and a request, gets the sent data form filtering entities on
     * a list. If no form is sent, no data is returned (empty array).
     */
    final protected function getFilterData(
        QueryBuilder $qb,
        FormInterface $form,
        Request $request
    ): array {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return $form->getData();
        }

        return $form->getNormData() ?? [];
    }

    /**
     * Receives the needed data to search by likeness on a set of the entity
     * fields.
     *
     * @param array $data       The data sent into the form
     * @param array $attributes The attributes to use in the search
     */
    final protected function applyFilterByLikeness(
        QueryBuilder $qb,
        array $data,
        array $attributes
    ): QueryBuilder {
        if (strlen($data['search']) > 0) {
            $conditions = $qb->expr()->orx();
            foreach ($attributes as $attribute) {
                $conditions->add($qb->expr()->like($attribute, "'%" . $data['search'] . "%'"));
            }
            $qb->andWhere($conditions);
        }

        return $qb;
    }

    /**
     * Applies the needed table joins on the query builder in the sorting parameters.
     */
    final protected function applyFilterJoins(
        QueryBuilder $qb,
        Request $request
    ): QueryBuilder {
        $sortables = $this->get('tn.utils.generator.sortable')->get($request->get('_route'));
        foreach ($sortables as $sortable) {
            if (isset($sortable['join_class']) && $request->get('sort') == $sortable['database_name']) {
                $class = $sortable['join_class'];
                $field = $sortable['field'];
                $relation = 'e.' . $field . ' = ' . $sortable['field'];

                $qb->leftJoin($class, $field, 'WITH', $relation);
            }
        }

        return $qb;
    }

    protected function getExtraParameters($entity = null): array
    {
        return [];
    }

    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function generateEntityForm(?object $entity = null, array $attr = [])
    {
        return $this->createForm($this->formClass, $entity, $attr);
    }

    /**
     * Generates the title data from to be used at the export action.
     *
     * @param array $data optional, to used as needed to create the titles
     *
     * @return array<string>
     */
    protected function generateExportTitles(TranslatorInterface $translator, ?array $data = null): array
    {
        return [];
    }

    /**
     * Generates the export data from a given array object list to be used at
     * the export action.
     *
     * @param array<Subscribe> $list
     *
     * @return array<string>
     */
    protected function generateExportData(array $list): array
    {
        return [];
    }

    /**
     * Generates the translated string for the exported from the list CSV file.
     */
    protected function generateExportFileName(TranslatorInterface $translator): string
    {
        return $translator->trans($this->entityTranslationDomain . 'export.title') . '-' . date('YmdHis') . '.csv';
    }

    /**
     * Generates an csv file from Title and Data given as array.
     *
     * @todo send this method to another class. It is not suposed to be here.
     */
    final protected function generateCSV(array $titles, array $array): string
    {
        if (0 == count($array)) {
            return null;
        }

        ob_start();
        $pointer = fopen('php://output', 'w');
        fputcsv($pointer, $titles);
        foreach ($array as $row) {
            fputcsv($pointer, $row);
        }
        fclose($pointer);

        return ob_get_clean();
    }

    /**
     * Validates the controller attributes and throws excpetions in case of
     * failure or problems.
     *
     * @throws \Exception A simple exception to inform a problem with the
     *                    configuration of the controller
     */
    private function validate(): void
    {
        if (null === $this->formClass) {
            throw new \Exception('O parâmetro formClass deve ser atribuido.');
        }

        if (null === $this->formTemplate) {
            throw new \Exception('O parâmetro formTemplate deve ser atribuido.');
        }

        if (null === $this->formFilterClass) {
            throw new \Exception('O parâmetro formFilterClass deve ser atribuido.');
        }

        if (null === $this->entityClass) {
            throw new \Exception('O parâmetro entityClass deve ser atribuido.');
        }

        if (null === $this->entityTranslationDomain) {
            throw new \Exception('O parâmetro entityTranslationDomain deve ser atribuido.');
        }

        if (!in_array($this->methodOfExclusion, ['deactivate', 'lock', 'remove'])) {
            throw new \Exception('O parâmetro methodOfExclusion deve estar definido e ser uma das opções válidas (deactivate, lock, remove).');
        }
    }
}
