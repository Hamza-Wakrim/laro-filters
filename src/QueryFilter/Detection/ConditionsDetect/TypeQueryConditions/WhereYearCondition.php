<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions;

use LaroFilters\QueryFilter\Detection\Contract\ConditionsContract;
use LaroFilters\QueryFilter\Queries\DB\WhereYear;
use LaroFilters\QueryFilter\Queries\DB\WhereYearQuery;

/**
 * Class WhereYearCondition.
 */
class WhereYearCondition implements ConditionsContract
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
        if (!empty($params['year'])) {
            $method = WhereYearQuery::class;
        }

        return $method ?? null;
    }
} 