<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\ConditionsContract;
use LaroFilters\QueryFilter\Queries\DB\WhereMonth;
use LaroFilters\QueryFilter\Queries\DB\WhereMonthQuery;

/**
 * Class WhereMonthCondition.
 */
class WhereMonthCondition implements ConditionsContract
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
        if (!empty($params['month'])) {
            $method = WhereMonthQuery::class;
        }

        return $method ?? null;
    }
} 