<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Queries\BaseClause;

/**
 * Class WhereNull.
 */
class WhereNull extends BaseClause
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
        return $query->whereNull($this->filter);
    }
} 