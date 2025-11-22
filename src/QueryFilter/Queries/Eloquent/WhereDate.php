<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;

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
