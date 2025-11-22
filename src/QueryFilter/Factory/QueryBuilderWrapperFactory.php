<?php

namespace LaroFilters\QueryFilter\Factory;

use LaroFilters\QueryFilter\Core\DbBuilder\DbBuilderWrapper;
use LaroFilters\QueryFilter\Core\DbBuilder\DbBuilderWrapperInterface;
use LaroFilters\QueryFilter\Core\EloquentBuilder\EloquentModelBuilderWrapper;

/**
 *
 */
class QueryBuilderWrapperFactory
{
    /**
     * @param $builder
     * @return \LaroFilters\QueryFilter\Core\EloquentBuilder\EloquentModelBuilderWrapper
     */
    public static function createEloquentQueryBuilder($builder): EloquentModelBuilderWrapper
    {
        return new EloquentModelBuilderWrapper($builder);
    }

    /**
     * @param $builder
     * @return \LaroFilters\QueryFilter\Core\DbBuilder\DbBuilderWrapperInterface
     */
    public static function createDbQueryBuilder($builder): DbBuilderWrapperInterface
    {
        return new DbBuilderWrapper($builder);
    }
}
