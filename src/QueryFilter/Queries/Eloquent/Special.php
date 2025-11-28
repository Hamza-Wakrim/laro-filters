<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\SpecialCondition;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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
     * @throws LaroFiltersException
     *
     */
    public function apply($query)
    {
        try {
            if (empty($this->values) || !is_array($this->values)) {
                Log::warning('LaroFilters: Empty or invalid values in Special::apply()', [
                    'values' => $this->values,
                ]);
                return $query;
            }

            foreach ($this->values as $key => $param_value) {
                try {
                    if (!in_array($key, self::$reserve_param[SpecialCondition::PARAM_NAME])) {
                        Log::error('LaroFilters: Invalid parameter in f_params', [
                            'key' => $key,
                            'allowed_params' => self::$reserve_param[SpecialCondition::PARAM_NAME],
                        ]);
                        throw new LaroFiltersException("$key is not in f_params array.", 2);
                    }
                    
            if (is_array($param_value)) {
                        if (!isset($this->values['orderBy']['field'])) {
                            Log::error('LaroFilters: Missing orderBy field in f_params', [
                                'values' => $this->values,
                            ]);
                            throw new LaroFiltersException("orderBy field is required in f_params.", 2);
                        }

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
                        
                        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                            Log::warning('LaroFilters: Invalid orderBy direction, defaulting to ASC', [
                                'direction' => $direction,
                            ]);
                            $direction = 'ASC';
                        }
                        
                        foreach ($orderByFields as $order_by) {
                            try {
                                $this->applyOrderBy($query, trim((string)$order_by), $direction);
                            } catch (\Exception $e) {
                                Log::error('LaroFilters: Error applying orderBy in Special::apply()', [
                                    'order_by' => $order_by,
                                    'direction' => $direction,
                                    'error' => $e->getMessage(),
                                ]);
                                throw new LaroFiltersException("Error applying orderBy '{$order_by}': " . $e->getMessage(), 4);
                            }
                        }
                    } else {
                        // Handle limit
                        if (!is_numeric($param_value) || $param_value < 0) {
                            Log::error('LaroFilters: Invalid limit value in f_params', [
                                'limit' => $param_value,
                            ]);
                            throw new LaroFiltersException("Limit must be a positive number.", 2);
                        }

                        $maxLimit = config('laroFilters.max_limit', 0);
                        if ($maxLimit > 0) {
                            $param_value = min($maxLimit, (int)$param_value);
                        }
                        
                        try {
                            $query->limit((int)$param_value);
                        } catch (\Exception $e) {
                            Log::error('LaroFilters: Error applying limit in Special::apply()', [
                                'limit' => $param_value,
                                'error' => $e->getMessage(),
                            ]);
                            throw new LaroFiltersException("Error applying limit: " . $e->getMessage(), 4);
                        }
                    }
                } catch (LaroFiltersException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    Log::error('LaroFilters: Error processing f_params key in Special::apply()', [
                        'key' => $key,
                        'param_value' => $param_value,
                        'error' => $e->getMessage(),
                    ]);
                    throw new LaroFiltersException("Error processing f_params['{$key}']: " . $e->getMessage(), 4);
                }
            }

            return $query;
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in Special::apply()', [
                'values' => $this->values ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in Special::apply(): " . $e->getMessage(), 4);
        }
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
        try {
            if (empty($orderBy)) {
                Log::error('LaroFilters: Empty orderBy in applyRelationOrderBy()');
                throw new LaroFiltersException("OrderBy field cannot be empty.", 4);
            }

            $parts = explode('.', $orderBy);
            
            if (count($parts) < 2) {
                Log::error('LaroFilters: Invalid relation orderBy format', [
                    'orderBy' => $orderBy,
                ]);
                throw new LaroFiltersException("Invalid relation orderBy format: '{$orderBy}'. Expected 'relation.field'.", 4);
            }

            $field = array_pop($parts);
            $relationName = array_shift($parts);

            if (empty($field) || empty($relationName)) {
                Log::error('LaroFilters: Empty field or relation name in applyRelationOrderBy()', [
                    'orderBy' => $orderBy,
                    'field' => $field,
                    'relationName' => $relationName,
                ]);
                throw new LaroFiltersException("Field and relation name cannot be empty in orderBy: '{$orderBy}'.", 4);
            }

            $model = $query->getModel();
            
            if (!$model) {
                Log::error('LaroFilters: Model is null in applyRelationOrderBy()');
                throw new LaroFiltersException("Query model is null.", 4);
            }
            
            // Check if relation exists
            if (!method_exists($model, $relationName)) {
                Log::error('LaroFilters: Relation not found on model', [
                    'relation' => $relationName,
                    'model' => get_class($model),
                ]);
                throw new LaroFiltersException("Relation '{$relationName}' not found on model " . get_class($model) . ".", 3);
            }

            // Get the relation instance
            try {
                $relation = $model->{$relationName}();
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error getting relation instance', [
                    'relation' => $relationName,
                    'model' => get_class($model),
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error getting relation '{$relationName}': " . $e->getMessage(), 4);
            }

            if (!$relation) {
                Log::error('LaroFilters: Relation instance is null', [
                    'relation' => $relationName,
                ]);
                throw new LaroFiltersException("Relation '{$relationName}' returned null.", 4);
            }

            $relatedModel = $relation->getRelated();
            $relatedTable = $relatedModel->getTable();
            
            if (empty($relatedTable)) {
                Log::error('LaroFilters: Related table is empty', [
                    'relation' => $relationName,
                ]);
                throw new LaroFiltersException("Related table name is empty for relation '{$relationName}'.", 4);
            }
            
            // Get the main table
            $mainTable = $model->getTable();
            
            if (empty($mainTable)) {
                Log::error('LaroFilters: Main table is empty', [
                    'model' => get_class($model),
                ]);
                throw new LaroFiltersException("Main table name is empty.", 4);
            }
            
            // Determine join condition based on relation type
            $relationClass = get_class($relation);
            
            // Check if join already exists for this relation
            $joinAlias = $relatedTable . '_order_' . $relationName;
            try {
                $hasJoin = collect($query->getQuery()->joins ?? [])->contains(function ($join) use ($joinAlias) {
                    return isset($join->table) && strpos($join->table, $joinAlias) !== false;
                });
            } catch (\Exception $e) {
                Log::warning('LaroFilters: Error checking existing joins', [
                    'error' => $e->getMessage(),
                ]);
                $hasJoin = false;
            }

            if (!$hasJoin) {
                try {
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
                } catch (\Illuminate\Database\QueryException $e) {
                    Log::error('LaroFilters: Database error in applyRelationOrderBy join', [
                        'relation' => $relationName,
                        'relationClass' => $relationClass,
                        'error' => $e->getMessage(),
                        'sql' => $e->getSql() ?? null,
                    ]);
                    throw new LaroFiltersException("Database error joining relation '{$relationName}': " . $e->getMessage(), 4);
                } catch (\Exception $e) {
                    Log::error('LaroFilters: Error joining relation in applyRelationOrderBy', [
                        'relation' => $relationName,
                        'relationClass' => $relationClass,
                        'error' => $e->getMessage(),
                    ]);
                    throw new LaroFiltersException("Error joining relation '{$relationName}': " . $e->getMessage(), 4);
                }
            }

            // Order by the joined table's column
            try {
                $query->orderBy("{$joinAlias}.{$field}", $direction);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error applying orderBy on joined table', [
                    'joinAlias' => $joinAlias,
                    'field' => $field,
                    'direction' => $direction,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error ordering by '{$joinAlias}.{$field}': " . $e->getMessage(), 4);
            }
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in applyRelationOrderBy()', [
                'orderBy' => $orderBy ?? null,
                'direction' => $direction ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in applyRelationOrderBy(): " . $e->getMessage(), 4);
        }
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
        try {
            $model = $query->getModel();
            
            if (!$model) {
                Log::error('LaroFilters: Model is null in applyRelationOrderBySubquery()');
                throw new LaroFiltersException("Query model is null.", 4);
            }

            try {
                $relation = $model->{$relationName}();
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error getting relation in applyRelationOrderBySubquery', [
                    'relation' => $relationName,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error getting relation '{$relationName}': " . $e->getMessage(), 4);
            }

            $relatedModel = $relation->getRelated();
            $relatedTable = $relatedModel->getTable();
            
            if (empty($relatedTable)) {
                Log::error('LaroFilters: Related table is empty in applyRelationOrderBySubquery', [
                    'relation' => $relationName,
                ]);
                throw new LaroFiltersException("Related table name is empty for relation '{$relationName}'.", 4);
            }
            
            // Get relation type to determine ordering strategy
            $relationClass = get_class($relation);
            
            try {
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
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('LaroFilters: Database error in applyRelationOrderBySubquery', [
                    'relation' => $relationName,
                    'field' => $field,
                    'direction' => $direction,
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql() ?? null,
                ]);
                throw new LaroFiltersException("Database error in subquery for relation '{$relationName}': " . $e->getMessage(), 4);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error creating subquery in applyRelationOrderBySubquery', [
                    'relation' => $relationName,
                    'field' => $field,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error creating subquery for relation '{$relationName}': " . $e->getMessage(), 4);
            }
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in applyRelationOrderBySubquery()', [
                'relationName' => $relationName ?? null,
                'field' => $field ?? null,
                'direction' => $direction ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in applyRelationOrderBySubquery(): " . $e->getMessage(), 4);
        }
    }
}
