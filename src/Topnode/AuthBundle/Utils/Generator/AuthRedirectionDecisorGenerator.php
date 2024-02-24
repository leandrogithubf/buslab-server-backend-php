<?php

namespace App\Topnode\AuthBundle\Utils\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;

class AuthRedirectionDecisorGenerator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * The data being handled by this class.
     *
     * @var array[string]
     */
    private $data;

    public function __construct(
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router
    ) {
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;

        $kernel = $this->container->get('kernel');
        $this->token = $container->get('security.token_storage')->getToken();

        $resources = [
            $kernel->getProjectDir() . '/config/topnode/auth_redirection_decisor.yml',
        ];

        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $aux = explode('\\', $bundle);
            $bundle = end($aux);

            $resources[] = $this->container
                ->get('kernel')
                ->locateResource('@' . $bundle)
                . 'Resources/config/topnode/auth_redirection_decisor.yml'
            ;
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();

        $this->data = [];
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
     * CAUTION: If the user role is not in the keys it DOES NOT MEAN he is now
     * allowed. He may be allowed to access that route by inheriting the
     * permissions on security.yaml
     *
     * @param string $route
     *
     * @return mixed The array of all routes or the route itself for the role
     */
    public function get(?string $role = null)
    {
        if (0 !== strlen($role) && array_key_exists($role, $this->data[])) {
            return $this->data[$role];
        }

        return $this->data;
    }

    /**
     * Generates a redirection response for the authenticated user role.
     *
     * @throws \Exception informs that the user does not have configuration to
     *                    be redirected by this method
     */
    public function redirect(?UserInterface $user = null): RedirectResponse
    {
        foreach ($this->get() as $role => $route) {
            if ($this->authorizationChecker->isGranted($role, $user)) {
                return new RedirectResponse($this->router->generate($route));
            }
        }

        throw new \Exception('System is not prepared to redirect this user.');
    }
}
