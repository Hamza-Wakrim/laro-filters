<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 * Class WhereYearQuery.
 */
class WhereYearQuery extends BaseClause
{
    /**
     * @param $query
     *
     * @return Builder
     */
    public function apply($query)
    {
        return $query->whereYear($this->filter, $this->values['year']);
    }
} 