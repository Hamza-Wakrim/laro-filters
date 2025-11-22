<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 * Class WhereMonthQuery.
 */
class WhereMonthQuery extends BaseClause
{
    /**
     * @param $query
     *
     * @return Builder
     */
    public function apply($query)
    {
        return $query->whereMonth($this->filter, $this->values['month']);
    }
} 