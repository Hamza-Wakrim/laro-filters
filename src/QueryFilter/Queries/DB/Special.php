<?php

namespace LaroFilters\QueryFilter\Queries\DB;

use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\SpecialCondition;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Query\Builder;

/**
 * Class Special.
 */
class Special extends BaseClause
{
    /**
     * @var array
     */
    public static $reserve_param = [
        SpecialCondition::PARAM_NAME => [
            'limit',
            'orderBy',
        ],
    ];

    /**
     * @param $query
     *
     * @return Builder
     * @throws \Exception
     *
     */
    public function apply($query)
    {
        foreach ($this->values as $key => $param_value) {
            if (!in_array($key, self::$reserve_param[SpecialCondition::PARAM_NAME])) {
                throw new LaroFiltersException("$key is not in f_params array.", 2);
            }
            if (is_array($param_value)) {
                $orderByFields = $this->values['orderBy']['field'];
                // Convert to array if it's a string (comma-separated)
                if (is_string($orderByFields)) {
                    $orderByFields = explode(',', $orderByFields);
                }
                // Ensure it's an array
                if (!is_array($orderByFields)) {
                    $orderByFields = [$orderByFields];
                }
                
                $direction = is_string($this->values['orderBy']['type']) 
                    ? $this->values['orderBy']['type'] 
                    : 'ASC';
                
                foreach ($orderByFields as $order_by) {
                    $this->applyOrderBy($query, trim((string)$order_by), $direction);
                }
            } else {
                if (config('laroFilters.max_limit') > 0) {
                    $param_value = min(config('laroFilters.max_limit'), $param_value);
                }
                $query->limit($param_value);
            }
        }

        return $query;
    }

    /**
     * Apply orderBy, handling both direct columns and table.column notation
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $orderBy
     * @param string $direction
     * @return void
     */
    protected function applyOrderBy($query, string $orderBy, string $direction): void
    {
        // For DB builder, if orderBy contains a dot, it's already in table.column format
        // Just apply it directly (assuming the join is already done or will be done)
        $query->orderBy($orderBy, $direction);
    }
}
