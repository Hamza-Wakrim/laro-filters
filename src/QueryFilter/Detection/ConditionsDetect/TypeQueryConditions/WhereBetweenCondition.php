<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\DefaultConditionsContract;

/**
 * Class WhereBetweenCondition.
 */
class WhereBetweenCondition implements DefaultConditionsContract
{
    /**
     * @param $field
     * @param $params
     *
     * @return string|null
     */
    public static function detect($field, $params): ?string
    {
        if (isset($params['start']) && isset($params['end'])) {
            $method = 'WhereBetween';
        }

        return $method ?? null;
    }
}
