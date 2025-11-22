<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;


use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;

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
    public function apply($query): Builder
    {
        return $query->whereYear($this->filter, $this->values['year']);
    }
} 