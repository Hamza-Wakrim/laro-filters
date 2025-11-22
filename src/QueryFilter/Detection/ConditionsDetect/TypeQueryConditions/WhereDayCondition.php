<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\ConditionsContract;
use LaroFilters\QueryFilter\Queries\DB\WhereDay;
use LaroFilters\QueryFilter\Queries\DB\WhereDayQuery;

/**
 * Class WhereDayCondition.
 */
class WhereDayCondition implements ConditionsContract
{
    /**
     * @param $field
     * @param $params
     * @param $is_override_method
     *
     * @return string|null
     */
    public static function detect($field, $params, $is_override_method = false): ?string
    {
        if (!empty($params['day'])) {
            $method = WhereDayQuery::class;
        }

        return $method ?? null;
    }
} 