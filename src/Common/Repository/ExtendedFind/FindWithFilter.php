<?php

namespace App\Common\Repository\ExtendedFind;

use Doctrine\ORM\QueryBuilder;
use ValueError;

trait FindWithFilter
{
    public function addFiltersToQuery(QueryBuilder $query, array $filterBy, array $searchBy): QueryBuilder
    {
        $query = $this->addFilters($query, $filterBy);
        $query = $this->addSearches($query, $searchBy);
        return $query;
    }

    private function addFilters(QueryBuilder $query, array $filterBy)
    {
        foreach ($filterBy as $columnFilter) {
            $paramName = str_replace('.', '_', $columnFilter['column']);
            $column = $columnFilter['column'];
            $operator = $columnFilter['operator'];
            $value = $columnFilter['value'];

            if (!in_array($operator, ['like', '=', '<', '>', 'in', 'is'])) {
                throw new ValueError("Передан недопустимый оператор для фильтрации `$operator`.");
            }


            if ($value == null && $operator === 'is') {
                $query->andWhere("$column is NULL");
            } else {
                if ($operator === 'is') {
                    $operator = '=';
                }

                if (is_array($value) && $operator === 'in') {
                    $query->andWhere("$column IN (:$paramName)");
                } else {
                    if ($operator === 'like') {
                        $column = "LOWER($column)";
                        $lowerCaseValue = mb_strtolower($value);
                        $value = "%$lowerCaseValue%";
                    }

                    $query->andWhere("$column $operator :$paramName");
                }

                $query->setParameter(":$paramName", $value);
            }
        }

        return $query;
    }

    private function addSearches(QueryBuilder $query, array $searchBy)
    {
        if (count($searchBy) == 0) {
            return $query;
        }

        $filter_row = array();
        foreach ($searchBy as $columnFilter) {
            $paramName = str_replace('.', '_', $columnFilter['column']);
            $column = $columnFilter['column'];
            $operator = $columnFilter['operator'];
            $value = $columnFilter['value'];

            if (!in_array($operator, ['like', '=', '<', '>', 'in'])) {
                throw new ValueError("Передан недопустимый оператор для фильтрации `$operator`.");
            }


            if (is_array($value) && $operator === 'in') {
                $filter_row[] = "$column IN (:$paramName)";
            } else {
                if ($operator === 'like') {
                    $column = "LOWER($column)";
                    $lowerCaseValue = mb_strtolower($value);
                    $value = "%$lowerCaseValue%";
                }
                $filter_row[] = "$column $operator :$paramName";
            }
            $query->setParameter(":$paramName", $value);
        }

        $filter = join(" OR ", $filter_row);
        $query->andWhere("$filter");
        return $query;
    }
}
