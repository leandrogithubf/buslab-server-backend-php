<?php

namespace App\Topnode\BaseBundle\Twig;

use App\Topnode\BaseBundle\Utils\Configurator\Environment;
use App\Topnode\BaseBundle\Utils\Generator\BreadcrumbGenerator;
use App\Topnode\BaseBundle\Utils\Generator\SortableGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var \App\Topnode\BaseBundle\Utils\Configurator\Environment
     */
    private $environment;

    /**
     * Configuration of the bundle, parsed and loaded on the start of the request.
     *
     * @var array
     */
    private $config;

    /**
     * @var \App\Topnode\BaseBundle\Utils\Generator\BreadcrumbGenerator
     */
    private $breadcrumb;

    /**
     * @var \Topnode\BreadcrumbBundle\Generator\SortableGenerator
     */
    private $sortable;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    public function __construct(
        RequestStack $requestStack,
        Environment $environment,
        BreadcrumbGenerator $breadcrumbGenerator,
        SortableGenerator $sortableGenerator
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->environment = $environment;
        $this->breadcrumb = $breadcrumbGenerator;
        $this->sortable = $sortableGenerator;
    }

    /**
     * Called on Topnode\BaseBundle\DependencyInjection\TopnodeBaseExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        $this->environment->setConfig($config);
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('property_exists', function ($class, $property) {
                if (is_object($class)) {
                    $class = get_class($class);
                }

                return property_exists($class, $property);
            }),
        ];
    }

    public function getGlobals()
    {
        $globals = [
            'system' => [
                'since' => $this->environment->get('since'),
                'name' => $this->environment->get('name'),
                'slogan' => $this->environment->get('slogan'),
                'description' => $this->environment->get('description'),
                'logo' => $this->environment->get('logo'),
                'front' => $this->environment->get('front'),
            ],
            'icon_family' => $this->config['icon_family'],
            'icons' => $this->config['icons'],
        ];

        if (is_object($this->request)) {
            $route = $this->request->get('_route');
            $globals += [
                'page_title' => $this->breadcrumb->getTitle($route),
                'page_sortable' => $this->sortable->get($route),
                'page_breadcrumbs' => $this->breadcrumb->get($route),
            ];
        }

        return $globals;
    }

    public function getTests()
    {
        return [
            new \Twig_SimpleTest('paginated', function ($list) {
                return $list instanceof \Knp\Component\Pager\Pagination\PaginationInterface;
            }),
            new \Twig_SimpleTest('instanceof', function ($data, $class) {
                return $data instanceof $class;
            }),
            new \Twig_SimpleTest('double', function ($data) {
                return is_double($data);
            }),
        ];
    }

    public function getName()
    {
        return 'tn_base_extension';
    }
}
