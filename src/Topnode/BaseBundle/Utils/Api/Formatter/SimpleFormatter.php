<?php

namespace App\Topnode\BaseBundle\Utils\Api\Formatter;

use Doctrine\ORM\Tools\Pagination\Paginator;

class SimpleFormatter implements FormatterInterface
{
    public function format($value)
    {
        if (is_array($value) || ($value instanceof \Traversable)) {
            return $this->formatCollection($value);
        }

        return $this->formatEntity($value);
    }

    /**
     * Formats an entity in HATEOAS format.
     *
     * @param mixed $entity
     *
     * @return mixed
     */
    public function formatEntity($entity)
    {
        return $entity;
    }

    /**
     * Formats a collection in HATEOAS format.
     *
     * @param mixed $collection
     */
    public function formatCollection($collection): array
    {
        if (!is_array($collection) && $collection instanceof \Traversable) {
            $list = [];

            if ($collection instanceof Paginator && $collection->getQuery() instanceof \Doctrine\ORM\Query) {
                foreach ($collection->getQuery()->getResult() as $value) {
                    $list[] = $value;
                }
            } else {
                foreach ($collection as $value) {
                    $list[] = $value;
                }
            }
        }

        if ($collection instanceof Paginator) {
            $query = $collection->getQuery();
            $totalItems = $collection->count();

            $pageSize = $query->getMaxResults();
            if (is_null($pageSize)) {
                $currentPage = 1;
                $pageCount = 1;
            } else {
                $offset = $query->getFirstResult();
                $currentPage = (($offset / $pageSize) + 1);
                $pageCount = ($collection->count() / $pageSize);
                if ($pageCount < 1) {
                    $pageCount = 1;
                }
            }

            return [
                'collection' => $list,
                'count' => count($list),
                'current_page' => $currentPage,
                'page_count' => $pageCount,
                'page_size' => $pageSize,
                'total_items' => $totalItems,
            ];
        }

        return $list;
    }
}
