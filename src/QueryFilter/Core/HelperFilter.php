<?php

namespace LaroFilters\QueryFilter\Core;

use Illuminate\Support\Arr;

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
     */
    public static function convertRelationArrayRequestToStr($field, array $args): ?array
    {
        $arg_last = Arr::last($args);

        if (is_array($arg_last)) {
            $out = Arr::dot($args, $field.'.');
            
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
                    // Remove numeric indices (e.g., .0, .1, .2, etc.) from the end of the key
                    $cleanKey = preg_replace('/\.\d+$/', '', $key);
                    if (!isset($new[$cleanKey])) {
                        $new[$cleanKey] = [];
                    }
                    $new[$cleanKey][] = $item;
                }
                $out = $new;
            } elseif (!self::isAssoc($arg_last)) {
                // Handle case where arg_last itself is numeric array
                $new = [];
                foreach ($out as $key => $item) {
                    $index = $key;
                    for ($i = 0; $i <= 9; $i++) {
                        $index = rtrim($index, '.'.$i);
                    }
                    $new[$index][] = $out[$key];
                }
                $out = $new;
            } else {
                // Check for between condition (start/end)
                $key_search_start = $field.'.'.key($args).'.start';
                $key_search_end = $field.'.'.key($args).'.end';

                if (Arr::exists($out, $key_search_start) && Arr::exists($out, $key_search_end)) {
                    foreach ($args as $key => $item) {
                        $new[$field.'.'.$key] = $args[$key];
                    }
                    $out = $new;
                }
            }
        } else {
            $out = Arr::dot($args, $field.'.');
        }

        return $out;
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    public static function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param $request
     * @param array|null $keys
     *
     * @return array
     */
    public static function array_slice_keys($request, ?array $keys = null): array
    {
        $request = (array) $request;

        if (empty($keys)) {
            return [];
        }

        return array_intersect_key($request, array_fill_keys($keys, '1'));
    }
}
