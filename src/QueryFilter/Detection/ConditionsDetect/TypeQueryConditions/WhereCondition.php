<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\DefaultConditionsContract;
use LaroFilters\QueryFilter\Queries\Eloquent\Where;

/**
 * Class WhereCondition.
 */
class WhereCondition implements DefaultConditionsContract
{
    /**
     * @param $field
     * @param $params
     *
     * @return string|null
     */
    public static function detect($field, $params): ?string
    {
        if (isset($params)) {
            $method = 'Where';
        }

        return $method ?? null;
    }
}
