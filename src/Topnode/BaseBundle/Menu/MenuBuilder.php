<?php

namespace App\Topnode\BaseBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Yaml\Yaml;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $decisionManager;

    private $token;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var Knp\Menu\MenuItem
     */
    private $menu;

    /**
     * The menu data list by route.
     *
     * @var array
     */
    private $data = [];

    public function __construct(
        FactoryInterface $factory,
        AccessDecisionManagerInterface $decisionManager,
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container
    ) {
        $this->factory = $factory;
        $this->decisionManager = $decisionManager;
        $this->token = $tokenStorage->getToken();
        $this->container = $container;

        $kernel = $this->container->get('kernel');

        $resources = [
            $kernel->getProjectDir() . '/config/topnode/menu.yml',
        ];

        $bundles = $this->container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $aux = explode('\\', $bundle);
            $bundle = end($aux);

            $resources[] = $this->container
                ->get('kernel')
                ->locateResource('@' . $bundle)
                . 'Resources/config/topnode/menu.yml'
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

    public function __call(string $name, array $arguments)
    {
        return $this->createMenu($name, $arguments[0]);
    }

    public function createMenu(string $name, array $options)
    {
        $this->menu = $this->factory->createItem('root');

        if (!array_key_exists($name, $this->data)) {
            return $this->menu;
        }

        if (isset($this->data[$name]['attr']['menu_id'])) {
            $this->menu->setChildrenAttribute('id', $this->data[$name]['attr']['menu_id']);
        }

        if (isset($this->data[$name]['attr']['menu_class'])) {
            $this->menu->setChildrenAttribute(
                'class',
                $this->data[$name]['attr']['menu_class']
            );
        }

        if (!array_key_exists($name, $this->data) || !array_key_exists('items', $this->data[$name])) {
            return $this->menu;
        }

        foreach ($this->data[$name]['items'] as $item) {
            // Checking if the current user is allowed to see this menu and if
            // the menu has any restrictions by role.
            if (
                array_key_exists('roles', $item) && count($item['roles']) > 0 // There is role restriction
                && ($this->token instanceof TokenInterface) // Is authenticated with valid token
                && !$this->decisionManager->decide($this->token, $item['roles']) // Has no permission
            ) {
                continue;
            }

            // Getting the menu root variable to receive a new menu item
            if (isset($item['parent'])) {
                $menu = $this->menu[$item['parent']];
            } else {
                $menu = $this->menu;
            }

            // Defines if it should use the route or the uri
            if (isset($item['route'])) {
                $type = 'route';
            } elseif (isset($item['uri'])) {
                $type = 'uri';
            } elseif (isset($item['url'])) {
                $item['uri'] = $item['url'];
                $type = 'uri';
            } else {
                $item['uri'] = '#';
                $type = 'uri';
            }

            // Creating the menu item
            $menu
                ->addChild($item['title'], [
                    $type => $item[$type],
                ])
                ->setAttribute('icon', $item['icon'])
            ;

            if (isset($item['url'])) {
                $menu[$item['title']]->setLinkAttribute('target', '_blank');
            }

            // Seting the class for the <li>
            $class = $this->data[$name]['attr']['item_class'];
            if (isset($item['class'])) {
                $class .= ' ' . $item['class'];
            }

            if (strlen($class) > 0) {
                $menu[$item['title']]->setAttribute('class', $class);
            }

            // Seting the class for the <a>
            if (isset($this->data[$name]['attr']['link_class'])) {
                $menu[$item['title']]->setLinkAttribute('class', $this->data[$name]['attr']['link_class']);
            }

            // Setting the parent and child class information, if given.
            if (isset($item['parent'])) {
                $menu->setLinkAttribute('aria-expanded', 'false');
                $menu->setChildrenAttribute('aria-expanded', 'false');

                if (array_key_exists('sub_item_class', $this->data[$name]['attr'])) {
                    $menu->setChildrenAttribute('class', $this->data[$name]['attr']['sub_item_class']);
                } else {
                    $menu->setChildrenAttribute('class', 'collapse');
                }

                $class = $menu->getAttribute('class');
                if (empty($class) && array_key_exists('parent_item_class', $this->data[$name]['attr'])) {
                    $menu->setAttribute('class', $this->data[$name]['attr']['parent_item_class']);
                }
            }
        }

        return $this->menu;
    }
}
