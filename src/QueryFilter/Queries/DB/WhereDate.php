<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

class WhereDate extends BaseClause
{
    /**
     * @param $query
     * @return Builder
     */
    public function apply($query)
    {
        return $query->whereDate($this->filter, $this->values);
    }
}
