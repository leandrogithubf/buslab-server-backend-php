<?php

namespace App\Topnode\BaseBundle\Utils\Paginator;

abstract class AbstractPaginator implements PaginatorInterface
{
    /**
     * Defines the pagination page size, the number os itens to be displayed per
     * page in a paginated list.
     *
     * @var int
     */
    protected $pageSize = 25;

    /**
     * Defines the current page being shown. Starts at 1 by default.
     *
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @param int $pageSize
     */
    public function setPageSize($pageSize): PaginatorInterface
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): integer
    {
        return $this->pageSize;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage): PaginatorInterface
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): integer
    {
        return $this->currentPage;
    }
}
