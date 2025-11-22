<?php

namespace LaroFilters\QueryFilter\Core\FilterBuilder\QueryBuilder;

use LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCore;
use LaroFilters\QueryFilter\Core\FilterBuilder\IO\RequestFilter;
use LaroFilters\QueryFilter\Core\FilterBuilder\IO\ResponseFilter;
use LaroFilters\QueryFilter\Core\HelperEloquentFilter;

/**
 * Class QueryFilterBuilder.
 */
abstract class QueryFilterBuilder
{
    use HelperEloquentFilter;

    /**
     * @param \LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCore $queryFilterCore
     * @param \LaroFilters\QueryFilter\Core\FilterBuilder\IO\RequestFilter $requestFilter
     * @param \LaroFilters\QueryFilter\Core\FilterBuilder\IO\ResponseFilter $responseFilter
     */
    public function __construct(public QueryFilterCore $queryFilterCore, public RequestFilter $requestFilter, public ResponseFilter $responseFilter)
    {
    }

    /**
     * @param $builder
     * @param array|null $detections_injected
     * @param array|null $black_list_detections
     *
     * @return mixed
     * @throws \ReflectionException
     */
    abstract public function apply($builder, array $detections_injected = null, array $black_list_detections = null): mixed;

}
