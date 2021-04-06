<?php

namespace App\Common\Repository\ExtendedFind;

final class ColumnMapper
{
    public static function mapColumns(array $columns, array $mapping, string $defaultAlias): array
    {
        if (count($columns) > 0) {
            foreach ($columns as &$column) {
                if (key_exists($column['column'], $mapping)) {
                    $column['column'] = $mapping[$column['column']];
                } else {
                    $column['column'] = "$defaultAlias.{$column['column']}";
                }
            }
        }


        return $columns;
    }
}
