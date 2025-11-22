<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;

/**
 * Class WhereNotNull.
 */
class WhereNotNull extends BaseClause
{
    /**
     * @param $query
     * @param $field
     * @param $params
     *
     * @return mixed
     */
    public function apply($query)
    {
        return $query->whereNotNull($this->filter);
    }
} 