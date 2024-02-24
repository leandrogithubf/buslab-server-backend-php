<?php

namespace App\Topnode\BaseBundle\Utils\Paginator;

use Knp\Component\Pager\Pagination\PaginationInterface as KnpPagination;
use Knp\Component\Pager\PaginatorInterface as KnpPaginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiPaginator extends AbstractPaginator
{
    /**
     * KNP Paginator instance for.
     *
     * @var Knp\Component\Pager\PaginatorInterface
     */
    protected $paginator;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /**
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        RequestStack $requestStack,
        KnpPaginator $paginator,
        UrlGeneratorInterface $router
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->paginator = $paginator;
        $this->router = $router;

        $page = (int) ($this->request->get('page'));
        if ($page > 0) {
            $this->currentPage = $page;
        }

        $pageSize = (int) ($this->request->get('page_size'));
        if ($pageSize > 0) {
            $this->pageSize = $pageSize;
        }
    }

    /**
     * @param mixed $query The query to be paginated
     *
     * @return PaginatorInterface
     */
    public function paginate($query)
    {
        $paginated = $this->paginator->paginate(
            $query,
            $this->currentPage,
            $this->pageSize
        );

        return [
            'meta' => $this->generateMetadata($paginated),
            'links' => $this->generateLinks($paginated),
            'data' => $paginated->getItems(),
        ];
    }

    private function generateMetadata(KnpPagination $paginated)
    {
        $paginationData = $paginated->getPaginationData();

        return [
            'item_count' => $paginationData['currentItemCount'],
            'current_page' => $paginationData['current'],
            'page_size' => $paginationData['numItemsPerPage'],
            'page_count' => $paginationData['pageCount'],
            'total_count' => $paginationData['totalCount'],
            'total_pages' => $paginationData['last'],
        ];
    }

    private function generateLinks(KnpPagination $paginated)
    {
        $paginationData = $paginated->getPaginationData();

        $params = $paginated->getParams();

        $links = [
            'cannonical' => '',
            'first' => '',
            'previous' => '',
            'next' => '',
            'last' => '',
        ];

        $route = $paginated->getRoute();
        $params = $paginated->getParams();

        foreach ($links as $key => $link) {
            $paramsAux = $params;
            if ('cannonical' === $key) {
                unset($paramsAux['page']);
            } elseif ('first' === $key) {
                $paramsAux['page'] = 1;
            } elseif ('last' === $key) {
                $paramsAux['page'] = $paginationData['last'];
            } elseif ('next' === $key) {
                if (!isset($paginationData['next'])) {
                    unset($links[$key]);
                    continue;
                }
                $paramsAux['page'] = $paginationData['next'];
            } elseif ('previous' === $key) {
                if (!isset($paginationData['previous'])) {
                    unset($links[$key]);
                    continue;
                }
                $paramsAux['page'] = $paginationData['previous'];
            }

            $route = $paginated->getRoute();
            $params = $paginated->getParams();

            $links[$key] = $this->router->generate($route, $paramsAux, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $links;
    }
}
