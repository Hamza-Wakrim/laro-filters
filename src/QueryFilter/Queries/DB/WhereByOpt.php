<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 * Class WhereByOpt.
 */
class WhereByOpt extends BaseClause
{
    /**
     * @param $query
     * @return Builder
     */
    public function apply($query)
    {
        $opt = $this->values['operator'];
        $value = $this->values['value'];

        return $query->where("$this->filter", "$opt", $value);
    }
}
