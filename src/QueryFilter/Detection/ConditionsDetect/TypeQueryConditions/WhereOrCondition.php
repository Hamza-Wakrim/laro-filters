<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\DefaultConditionsContract;

/**
 * Class WhereOrCondition.
 */
class WhereOrCondition implements DefaultConditionsContract
{

    const PARAM_NAME = 'or';

    /**
     * @param $field
     * @param $params
     *
     * @return string|null
     */
    public static function detect($field, $params): ?string
    {
        if ($field == self::PARAM_NAME) {
            $method = 'WhereOr';
        }

        return $method ?? null;
    }
}
