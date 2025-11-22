<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 * Class WhereBetween.
 */
class WhereBetween extends BaseClause
{
    /**
     * @param $query
     *
     * @return Builder
     */
    public function apply($query)
    {
        $start = $this->values['start'];
        $end = $this->values['end'];

        return $query->whereBetween($this->filter, [$start, $end]);
    }
}
