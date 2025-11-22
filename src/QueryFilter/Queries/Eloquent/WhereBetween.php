<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;

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
