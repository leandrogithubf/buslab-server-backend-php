<?php

namespace App\Topnode\BaseBundle\Utils\Paginator;

use Symfony\Component\HttpFoundation\RequestStack;

class DoctrinePaginator extends AbstractPaginator
{
    /**
     * @var [type]
     */
    private $request;

    /**
     * Defines.
     *
     * @var bool
     */
    private $isFetchJoinCollection = true;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        $page = (int) ($this->request->get('page'));
        if ($page > 0) {
            $this->currentPage = $page;
        }

        $this->pageSize = (int) ($this->request->get('page_size'));
    }

    public function paginate($query): object
    {
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $this->isFetchJoinCollection);

        $pageSize = $this->pageSize;
        if ($pageSize > 0) {
            $paginator->getQuery()
                ->setFirstResult($pageSize * ($this->currentPage - 1))
                ->setMaxResults($pageSize)
            ;
        }

        return $paginator;
    }

    /**
     * [setIsFetchJoinCollection description].
     *
     * @param bool $isFetchJoinCollection [description]
     */
    public function setIsFetchJoinCollection(boolean $isFetchJoinCollection): PaginatorInterface
    {
        $this->isFetchJoinCollection = $isFetchJoinCollection;
    }

    /**
     * @return bool
     */
    public function isFetchJoinCollection(): boolean
    {
        return $this->isFetchJoinCollection;
    }

    /**
     * @return bool
     */
    public function getIsFetchJoinCollection(): boolean
    {
        return $this->isFetchJoinCollection();
    }
}
