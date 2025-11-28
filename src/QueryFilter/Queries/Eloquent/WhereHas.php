<?php

namespace LaroFilters\QueryFilter\Queries\Eloquent;

use LaroFilters\QueryFilter\Queries\BaseClause;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Class WhereHas.
 */
class WhereHas extends BaseClause
{
    /**
     * @param $query
     *
     * @return Builder
     * @throws LaroFiltersException
     */
    public function apply($query)
    {
        try {
            if (empty($this->filter) || !is_string($this->filter)) {
                Log::error('LaroFilters: Invalid filter in WhereHas::apply()', [
                    'filter' => $this->filter,
                    'values' => $this->values,
                ]);
                throw new LaroFiltersException("Filter must be a non-empty string.", 4);
            }

            $field_row = explode('.', $this->filter);
            
            if (empty($field_row)) {
                Log::error('LaroFilters: Empty field_row after explode in WhereHas::apply()', [
                    'filter' => $this->filter,
                ]);
                throw new LaroFiltersException("Invalid filter format: '{$this->filter}'. Expected dot notation.", 4);
            }

            $field_row = end($field_row);

            if (empty($field_row)) {
                Log::error('LaroFilters: Empty field name in WhereHas::apply()', [
                    'filter' => $this->filter,
                ]);
                throw new LaroFiltersException("Field name cannot be empty in filter: '{$this->filter}'.", 4);
            }

            $conditions = str_replace('.'.$field_row, '', $this->filter);

            if (empty($conditions)) {
                Log::error('LaroFilters: Empty relation path in WhereHas::apply()', [
                    'filter' => $this->filter,
                    'field_row' => $field_row,
                ]);
                throw new LaroFiltersException("Relation path cannot be empty in filter: '{$this->filter}'.", 4);
            }

            $value = $this->values;

            try {
                return $query->whereHas(
                    $conditions,
                    function ($q) use ($value, $field_row) {
                        try {
                            $condition = 'where';
                            if (is_array($value)) {
                                $condition = 'whereIn';
                            }
                            
                            if (!method_exists($q, $condition)) {
                                throw new LaroFiltersException("Query builder method '{$condition}' does not exist.", 4);
                            }
                            
                            $q->$condition($field_row, $value);
                        } catch (\Exception $e) {
                            Log::error('LaroFilters: Error in WhereHas closure', [
                                'condition' => $condition ?? 'unknown',
                                'field_row' => $field_row,
                                'value' => $value,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }
                );
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('LaroFilters: Database query error in WhereHas::apply()', [
                    'filter' => $this->filter,
                    'conditions' => $conditions,
                    'field_row' => $field_row,
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql() ?? null,
                ]);
                throw new LaroFiltersException("Database error in whereHas query for '{$this->filter}': " . $e->getMessage(), 4);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error in WhereHas::apply()', [
                    'filter' => $this->filter,
                    'conditions' => $conditions,
                    'field_row' => $field_row,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error applying whereHas filter '{$this->filter}': " . $e->getMessage(), 4);
            }
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in WhereHas::apply()', [
                'filter' => $this->filter ?? null,
                'values' => $this->values ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in WhereHas::apply(): " . $e->getMessage(), 4);
        }
    }
}
