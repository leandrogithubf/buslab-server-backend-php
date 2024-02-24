<?php

namespace App\Topnode\BaseBundle\Utils\Paginator;

interface PaginatorInterface
{
    public function paginate($data);

    public function setPageSize($pageSize): PaginatorInterface;

    public function getPageSize(): integer;

    public function setCurrentPage($currentPage): PaginatorInterface;

    public function getCurrentPage(): integer;
}
