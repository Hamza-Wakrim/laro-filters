<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\DefaultConditionsContract;

/**
 * Class SpecialCondition.
 */
class SpecialCondition implements DefaultConditionsContract
{
    const PARAM_NAME = 'f_params';

    /**
     * @param $field
     * @param $params
     *
     * @return string|null
     */
    public static function detect($field, $params): ?string
    {
        if ($field == self::PARAM_NAME) {
            return 'Special';
        }

        return null;
    }
}
