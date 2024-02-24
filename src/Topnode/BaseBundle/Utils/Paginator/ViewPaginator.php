<?php

namespace App\Topnode\BaseBundle\Utils\Paginator;

use Knp\Component\Pager\PaginatorInterface as KnpPaginator;
use Symfony\Component\HttpFoundation\RequestStack;

class ViewPaginator extends AbstractPaginator
{
    /**
     * KNP Paginator instance for.
     *
     * @var Knp\Component\Pager\PaginatorInterface
     */
    protected $paginator;

    /**
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        RequestStack $requestStack,
        KnpPaginator $paginator
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->paginator = $paginator;

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
        return $this->paginator->paginate(
            $query,
            $this->currentPage,
            $this->pageSize
        );
    }
}
