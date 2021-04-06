<?php

namespace App\Common\Repository\ExtendedFind;

interface FindWithFilterAndSort
{
    public function findWithSortAndFilters(array $filterBy, array $orderBy, $limit = 10, $offset = 0);
}
