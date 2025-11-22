<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 *
 */
class Where extends BaseClause
{
    /**
     * @param $query
     * @return Builder
     */
    public function apply($query)
    {
        return $query->where($this->filter, $this->values);
    }
}
