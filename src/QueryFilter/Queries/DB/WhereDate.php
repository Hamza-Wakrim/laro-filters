<?php

namespace laroFilters\QueryFilter\Queries\DB;

use laroFilters\QueryFilter\Queries\BaseClause;
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
