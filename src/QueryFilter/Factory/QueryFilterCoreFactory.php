<?php

namespace LaroFilters\QueryFilter\Factory;

use LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCore;
use LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCoreBuilder;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\DB\DBBuilderQueryByCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\Eloquent\MainBuilderQueryByCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\SpecialCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereBetweenCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereByOptCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereDateCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereDayCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereDoesntHaveCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereHasCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereInCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereLikeCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereMonthCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereNullCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereOrCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereYearCondition;

/**
 * Class QueryFilterCoreFactory.
 */
class QueryFilterCoreFactory
{
    /**
     * @return \LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCore
     */
    public function createQueryFilterCoreEloquentBuilder(): QueryFilterCore
    {
        $mainBuilderConditions = new MainBuilderQueryByCondition();
        return app(QueryFilterCoreBuilder::class,
            [
                'defaultDetections' => $this->getDefaultDetectorsEloquent(),
                'injectedDetections' => null,
                'mainBuilderConditions' => $mainBuilderConditions
            ]
        );
    }

    /**
     * @return \LaroFilters\QueryFilter\Core\FilterBuilder\Core\QueryFilterCore
     */
    public function createQueryFilterCoreDBQueryBuilder(): QueryFilterCore
    {
        $mainBuilderConditions = new DBBuilderQueryByCondition();
        return app(QueryFilterCoreBuilder::class,
            [
                'defaultDetections' => $this->getDefaultDetectorsEloquent(),
                'injectedDetections' => null,
                'mainBuilderConditions' => $mainBuilderConditions
            ]
        );
    }

    /**
     * @return array
     * @note DON'T CHANGE ORDER THESE BASED ON FLIMSY REASON.
     */
    private function getDefaultDetectorsEloquent(): array
    {
        return [
            SpecialCondition::class,
            WhereBetweenCondition::class,
            WhereByOptCondition::class,
            WhereLikeCondition::class,
            WhereInCondition::class,
            WhereOrCondition::class,
            WhereHasCondition::class,
            WhereDoesntHaveCondition::class,
            WhereDateCondition::class,
            WhereNullCondition::class,
            WhereMonthCondition::class,
            WhereYearCondition::class,
            WhereDayCondition::class,
            WhereCondition::class,
        ];
    }
}
