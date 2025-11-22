<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class WhereCustom.
 */
class WhereCustom extends BaseClause
{
    /**
     * @param $query
     *
     * @return Builder
     */
    public function apply($query)
    {
        $method = $this->getMethod($this->filter);
        return $query->getModel()->$method($query, $this->values);
    }

    /**
     * @param $filter
     * @return string
     */
    public static function getMethod($filter): string
    {
        $custom_method_sign = config('laroFilters.custom_method_sign');

        $filter = ucfirst($filter);
        $method = $custom_method_sign . $filter;
        return $method;
    }
}
