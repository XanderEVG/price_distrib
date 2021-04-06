<?php

namespace App\Common\Repository\ExtendedFind;

use Doctrine\ORM\QueryBuilder;

trait FindWithSort
{
    public function addSortToQuery(QueryBuilder $query, array $orderBy): QueryBuilder
    {
        foreach ($orderBy as $columnOrder) {
            $query->orderBy($columnOrder['column'], $columnOrder['direction']);
        }

        return $query;
    }
}
