<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\SpecialCondition;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;

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
     * Apply orderBy, handling both direct columns and relation columns
     *
     * @param Builder $query
     * @param string $orderBy
     * @param string $direction
     * @return void
     */
    protected function applyOrderBy(Builder $query, string $orderBy, string $direction): void
    {
        // Check if this is a relation field (contains dot)
        if (strpos($orderBy, '.') !== false) {
            $this->applyRelationOrderBy($query, $orderBy, $direction);
        } else {
            // Direct column ordering
            $query->orderBy($orderBy, $direction);
        }
    }

    /**
     * Apply orderBy for relation columns
     *
     * @param Builder $query
     * @param string $orderBy (e.g., "posts.title" or "user.profile.name")
     * @param string $direction
     * @return void
     */
    protected function applyRelationOrderBy(Builder $query, string $orderBy, string $direction): void
    {
        $parts = explode('.', $orderBy);
        $field = array_pop($parts);
        $relationName = array_shift($parts);

        $model = $query->getModel();
        
        // Check if relation exists
        if (!method_exists($model, $relationName)) {
            throw new LaroFiltersException("Relation '{$relationName}' not found on model " . get_class($model) . ".", 3);
        }

        // Get the relation instance
        $relation = $model->{$relationName}();
        $relatedModel = $relation->getRelated();
        $relatedTable = $relatedModel->getTable();
        
        // Get the main table
        $mainTable = $model->getTable();
        
        // Determine join condition based on relation type
        $relationClass = get_class($relation);
        
        // Check if join already exists for this relation
        $joinAlias = $relatedTable . '_order_' . $relationName;
        $hasJoin = collect($query->getQuery()->joins ?? [])->contains(function ($join) use ($joinAlias) {
            return isset($join->table) && strpos($join->table, $joinAlias) !== false;
        });

        if (!$hasJoin) {
            // Handle different relation types
            if (strpos($relationClass, 'BelongsTo') !== false) {
                // BelongsTo: foreign key is on main table, owner key is on related table
                $foreignKey = $relation->getForeignKeyName();
                $ownerKey = $relation->getOwnerKeyName();
                
                $query->leftJoin(
                    "{$relatedTable} as {$joinAlias}",
                    "{$mainTable}.{$foreignKey}",
                    '=',
                    "{$joinAlias}.{$ownerKey}"
                );
            } elseif (strpos($relationClass, 'HasOne') !== false) {
                // HasOne: foreign key is on related table, owner key is on main table
                $foreignKey = $relation->getForeignKeyName();
                $ownerKey = $relation->getLocalKeyName();
                
                $query->leftJoin(
                    "{$relatedTable} as {$joinAlias}",
                    "{$joinAlias}.{$foreignKey}",
                    '=',
                    "{$mainTable}.{$ownerKey}"
                );
            } elseif (strpos($relationClass, 'HasMany') !== false) {
                // HasMany: Use subquery to avoid duplicate rows from join
                $this->applyRelationOrderBySubquery($query, $relationName, $field, $direction);
                return;
            } else {
                // For other relation types (BelongsToMany, MorphTo, etc.), use subquery
                $this->applyRelationOrderBySubquery($query, $relationName, $field, $direction);
                return;
            }
        }

        // Order by the joined table's column
        $query->orderBy("{$joinAlias}.{$field}", $direction);
    }

    /**
     * Apply orderBy using subquery for complex relations (HasMany, BelongsToMany, etc.)
     *
     * @param Builder $query
     * @param string $relationName
     * @param string $field
     * @param string $direction
     * @return void
     */
    protected function applyRelationOrderBySubquery(Builder $query, string $relationName, string $field, string $direction): void
    {
        $model = $query->getModel();
        $relation = $model->{$relationName}();
        $relatedModel = $relation->getRelated();
        $relatedTable = $relatedModel->getTable();
        
        // Get relation type to determine ordering strategy
        $relationClass = get_class($relation);
        
        // For HasMany, order by the first related record (MIN for ASC, MAX for DESC)
        if (strpos($relationClass, 'HasMany') !== false) {
            $aggregate = strtoupper($direction) === 'DESC' ? 'MAX' : 'MIN';
            
            $subquery = $relatedModel->newQuery()
                ->selectRaw("{$aggregate}({$relatedTable}.{$field})")
                ->whereColumn(
                    $relation->getForeignKeyName(),
                    $model->getTable() . '.' . $model->getKeyName()
                );
        } else {
            // For other relations, get the first related record
            $subquery = $relatedModel->newQuery()
                ->select($field)
                ->whereColumn(
                    $relation->getForeignKeyName(),
                    $model->getTable() . '.' . $model->getKeyName()
                )
                ->limit(1);
        }

        $query->orderByRaw("({$subquery->toSql()}) {$direction}", $subquery->getBindings());
    }
}
