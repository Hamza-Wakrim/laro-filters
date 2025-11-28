<?php

namespace LaroFilters\QueryFilter\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;

/**
 * Trait HelperFilter.
 */
trait HelperFilter
{
    /**
     * @param       $field
     * @param array $args
     *
     * @return array|null
     * @throws LaroFiltersException
     */
    public static function convertRelationArrayRequestToStr($field, array $args): ?array
    {
        try {
            if (empty($field) || !is_string($field)) {
                Log::error('LaroFilters: Invalid field parameter in convertRelationArrayRequestToStr', [
                    'field' => $field,
                    'args' => $args,
                ]);
                throw new LaroFiltersException("Field parameter must be a non-empty string.", 4);
            }

            if (empty($args) || !is_array($args)) {
                Log::warning('LaroFilters: Empty or invalid args in convertRelationArrayRequestToStr', [
                    'field' => $field,
                    'args' => $args,
                ]);
                return null;
            }

            $arg_last = Arr::last($args);

            if (is_array($arg_last)) {
                try {
                    $out = Arr::dot($args, $field.'.');
                } catch (\Exception $e) {
                    Log::error('LaroFilters: Error in Arr::dot()', [
                        'field' => $field,
                        'args' => $args,
                        'error' => $e->getMessage(),
                    ]);
                    throw new LaroFiltersException("Failed to convert relation array request for field '{$field}': " . $e->getMessage(), 4);
                }
                
                // Check if any keys end with numeric indices (e.g., .0, .1, .2)
                // This indicates a numeric array was used (for whereIn conditions)
                $hasNumericIndices = false;
                foreach ($out as $key => $item) {
                    // Check if key ends with .0, .1, .2, etc.
                    if (preg_match('/\.\d+$/', $key)) {
                        $hasNumericIndices = true;
                        break;
                    }
                }
                
                if ($hasNumericIndices) {
                    // Remove numeric indices from dot notation keys and group values
                    $new = [];
                    foreach ($out as $key => $item) {
                        try {
                            // Remove numeric indices (e.g., .0, .1, .2, etc.) from the end of the key
                            $cleanKey = preg_replace('/\.\d+$/', '', $key);
                            
                            if (empty($cleanKey)) {
                                Log::warning('LaroFilters: Empty cleanKey after removing numeric indices', [
                                    'original_key' => $key,
                                    'field' => $field,
                                ]);
                                continue;
                            }
                            
                            if (!isset($new[$cleanKey])) {
                                $new[$cleanKey] = [];
                            }
                            $new[$cleanKey][] = $item;
                        } catch (\Exception $e) {
                            Log::error('LaroFilters: Error processing key in convertRelationArrayRequestToStr', [
                                'key' => $key,
                                'field' => $field,
                                'error' => $e->getMessage(),
                            ]);
                            continue;
                        }
                    }
                    
                    if (empty($new)) {
                        Log::warning('LaroFilters: No valid keys after processing numeric indices', [
                            'field' => $field,
                            'original_out' => $out,
                        ]);
                        return null;
                    }
                    
                    $out = $new;
                } elseif (!self::isAssoc($arg_last)) {
                    // Handle case where arg_last itself is numeric array
                    $new = [];
                    foreach ($out as $key => $item) {
                        try {
                            $index = $key;
                            for ($i = 0; $i <= 9; $i++) {
                                $index = rtrim($index, '.'.$i);
                            }
                            
                            if (empty($index)) {
                                Log::warning('LaroFilters: Empty index after rtrim', [
                                    'original_key' => $key,
                                    'field' => $field,
                                ]);
                                continue;
                            }
                            
                            $new[$index][] = $out[$key];
                        } catch (\Exception $e) {
                            Log::error('LaroFilters: Error processing numeric array key', [
                                'key' => $key,
                                'field' => $field,
                                'error' => $e->getMessage(),
                            ]);
                            continue;
                        }
                    }
                    
                    if (empty($new)) {
                        Log::warning('LaroFilters: No valid keys after processing numeric array', [
                            'field' => $field,
                        ]);
                        return null;
                    }
                    
                    $out = $new;
                } else {
                    // Check for between condition (start/end)
                    $firstKey = key($args);
                    if ($firstKey !== null) {
                        $key_search_start = $field.'.'.$firstKey.'.start';
                        $key_search_end = $field.'.'.$firstKey.'.end';

                        if (Arr::exists($out, $key_search_start) && Arr::exists($out, $key_search_end)) {
                            $new = [];
                            foreach ($args as $key => $item) {
                                $new[$field.'.'.$key] = $args[$key];
                            }
                            $out = $new;
                        }
                    }
                }
            } else {
                try {
                    $out = Arr::dot($args, $field.'.');
                } catch (\Exception $e) {
                    Log::error('LaroFilters: Error in Arr::dot() for non-array last arg', [
                        'field' => $field,
                        'args' => $args,
                        'error' => $e->getMessage(),
                    ]);
                    throw new LaroFiltersException("Failed to convert relation array request for field '{$field}': " . $e->getMessage(), 4);
                }
            }

            return $out;
            
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in convertRelationArrayRequestToStr', [
                'field' => $field,
                'args' => $args,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error processing relation array request for field '{$field}': " . $e->getMessage(), 4);
        }
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    public static function isAssoc(array $arr): bool
    {
        try {
            if ([] === $arr) {
                return false;
            }

            $keys = array_keys($arr);
            $count = count($arr);
            
            if ($count === 0) {
                return false;
            }

            return $keys !== range(0, $count - 1);
        } catch (\Exception $e) {
            Log::error('LaroFilters: Error in isAssoc()', [
                'arr' => $arr,
                'error' => $e->getMessage(),
            ]);
            // Default to associative if we can't determine
            return true;
        }
    }

    /**
     * @param $request
     * @param array|null $keys
     *
     * @return array
     * @throws LaroFiltersException
     */
    public static function array_slice_keys($request, ?array $keys = null): array
    {
        try {
            $request = (array) $request;

            if (empty($keys)) {
                return [];
            }

            // Validate keys array
            foreach ($keys as $key) {
                if (!is_string($key) && !is_int($key)) {
                    Log::warning('LaroFilters: Invalid key type in array_slice_keys', [
                        'key' => $key,
                        'keys' => $keys,
                    ]);
                }
            }

            try {
                $filledKeys = array_fill_keys($keys, '1');
                return array_intersect_key($request, $filledKeys);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error in array_slice_keys', [
                    'request' => $request,
                    'keys' => $keys,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Failed to slice array keys: " . $e->getMessage(), 4);
            }
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in array_slice_keys', [
                'request' => $request,
                'keys' => $keys,
                'error' => $e->getMessage(),
            ]);
            throw new LaroFiltersException("Unexpected error in array_slice_keys: " . $e->getMessage(), 4);
        }
    }
}
