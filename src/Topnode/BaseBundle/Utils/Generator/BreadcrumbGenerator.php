<?php

namespace App\Topnode\BaseBundle\Utils\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Yaml\Yaml;

class BreadcrumbGenerator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var array[\Symfony\Component\Routing\RouteCollection]
     */
    private $routes;

    /**
     * The breadcrumb data list by route.
     *
     * @var array
     */
    private $data = [];

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        ContainerInterface $container,
        AccessDecisionManagerInterface $decisionManager
    ) {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->routes = $router->getRouteCollection()->all();
        $this->decisionManager = $decisionManager;
        $this->token = $container->get('security.token_storage')->getToken();

        $kernel = $this->container->get('kernel');

        $resources = [
            $kernel->getProjectDir() . '/config/topnode/breadcrumb.yml',
        ];

        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $aux = explode('\\', $bundle);
            $bundle = end($aux);

            $resources[] = $this->container
                ->get('kernel')
                ->locateResource('@' . $bundle)
                . 'Resources/config/topnode/breadcrumb.yml'
            ;
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();

        foreach ($resources as $resource) {
            if (!$fs->exists($resource)) {
                continue;
            }

            $data = Yaml::parseFile($resource);
            if (!is_array($data)) {
                continue;
            }

            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Returns the complete breadcrumb for the passed route.
     *
     * @param string $route
     */
    public function get($route): array
    {
        if (!array_key_exists($route, $this->data)) {
            return [];
        }

        if (array_key_exists('roles_not_allowed', $this->data[$route])) {
            if ($this->decisionManager->decide($this->token, $this->data[$route]['roles_not_allowed'])) {
                return [];
            }
        }

        $breadcrumbs = [
            $this->generateBreadcrumbData($route, true),
        ];

        while (isset($this->data[$route]['parent']) && !is_null($this->data[$route]['parent'])) {
            $route = $this->data[$route]['parent'];
            $breadcrumbs[] = $this->generateBreadcrumbData($route);
        }

        return array_reverse($breadcrumbs);
    }

    /**
     * Genarates the breadcrumb data structure to be used.
     */
    public function generateBreadcrumbData(string $route, bool $firstBreadcrumb = false): array
    {
        $routeIndex = $route;
        while (isset($this->data[$routeIndex]['parent']) && !isset($this->data[$routeIndex]['crud'])) {
            $routeIndex = $this->data[$routeIndex]['parent'];
        }

        return [
            'route' => $route,
            'name' => $this->getTitle($route),
            'url' => $this->generateUrl($route, $firstBreadcrumb),
            'data' => $this->routes[$route],
            'crud' => $this->getCrudRoutes($routeIndex),
            'crud_helpers' => $this->getCrudHelperRoutes($routeIndex),
            'custom' => $this->getCustomData($routeIndex),
            'form' => $this->getFormRoute($routeIndex),
        ];
    }

    /**
     * Returns the route title as defined on the breadcrumb file.
     *
     * @param string $route
     */
    public function getTitle($route): ?string
    {
        if (
            !array_key_exists($route, $this->data)
            || !array_key_exists('name', $this->data[$route])
        ) {
            return null;
        }

        return $this->data[$route]['name'];
    }

    /**
     * Generates the url for given route, if able to.
     */
    public function generateUrl(string $route, bool $firstBreadcrumb): ?string
    {
        try {
            if (!$firstBreadcrumb && array_key_exists('method_name', $this->data[$route])) {
                $methodName = $this->data[$route]['method_name'];

                return $this->router->generate($route, [
                    'id' => $this->request->get('entity')->{$methodName}()->getId(),
                ]);
            }

            return $this->router->generate($route);
        } catch (MissingMandatoryParametersException $e) {
            return null;
        }
    }

    /**
     * Returns the routes from the breadcrumb file in wich the user is allowed.
     *
     * @param string $route
     */
    public function getCrudRoutes($route): ?array
    {
        if (!array_key_exists('crud', $this->data[$route])) {
            return null;
        }

        foreach ($this->data[$route]['crud'] as $key => $routeCrud) {
            $routeHasData = array_key_exists($routeCrud, $this->data);
            $routeHasRoleControl = array_key_exists(
                'roles_not_allowed',
                $this->data[$routeCrud]
            );

            if ($routeHasData && $routeHasRoleControl) {
                $isAllowed = $this->decisionManager->decide(
                    $this->token,
                    $this->data[$routeCrud]['roles_not_allowed']
                );

                if ($isAllowed) {
                    unset($this->data[$route]['crud'][$key]);
                }
            }
        }

        return $this->data[$route]['crud'];
    }

    /**
     * Returns the routes from the breadcrumb file in wich the user is allowed
     * for helper routes (as reactivate an entity). It is an route that is part
     * of an CRUD but can not, or should not, be shown in the view.
     *
     * @param string $route
     */
    public function getCrudHelperRoutes($route): ?array
    {
        if (!array_key_exists('crud_helpers', $this->data[$route])) {
            return null;
        }

        foreach ($this->data[$route]['crud_helpers'] as $key => $routeCrud) {
            $routeHasData = array_key_exists($routeCrud, $this->data);
            $routeHasRoleControl = array_key_exists(
                'roles_not_allowed',
                $this->data[$routeCrud]
            );

            if ($routeHasData && $routeHasRoleControl) {
                $isAllowed = $this->decisionManager->decide(
                    $this->token,
                    $this->data[$routeCrud]['roles_not_allowed']
                );

                if ($isAllowed) {
                    unset($this->data[$route]['crud_helpers'][$key]);
                }
            }
        }

        return $this->data[$route]['crud_helpers'];
    }

    /**
     * Returns the routes from the breadcrumb file in wich the user is allowed
     * for helper routes (as reactivate an entity). It is an route that is part
     * of an CRUD but can not, or should not, be shown in the view.
     *
     * @param string $route
     *
     * @return string|null
     */
    public function getCustomData($route): array
    {
        if (!array_key_exists('custom', $this->data[$route])) {
            return [];
        }

        foreach ($this->data[$route]['custom'] as $key => $routeCrud) {
            if (array_key_exists($key, $this->data) && array_key_exists('roles_not_allowed', $this->data[$key])) {
                $rolesNotAllowed = $this->data[$key]['roles_not_allowed'];

                if ($this->decisionManager->decide($this->token, $rolesNotAllowed)) {
                    unset($this->data[$route]['custom'][$key]);
                }
            }
        }

        return $this->data[$route]['custom'];
    }

    /**
     * Returns the form generetor route if defined.
     *
     * @param string $route
     */
    public function getFormRoute($route): ?string
    {
        if (!array_key_exists('form', $this->data[$route])) {
            return null;
        }

        return $this->data[$route]['form'];
    }
}
