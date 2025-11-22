<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\DB;

use LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract;
use LaroFilters\QueryFilter\Queries\DB\Special;
use LaroFilters\QueryFilter\Queries\DB\Where;
use LaroFilters\QueryFilter\Queries\DB\WhereBetween;
use LaroFilters\QueryFilter\Queries\DB\WhereByOpt;
use LaroFilters\QueryFilter\Queries\DB\WhereCustom;
use LaroFilters\QueryFilter\Queries\DB\WhereDate;
use LaroFilters\QueryFilter\Queries\DB\WhereDoesntHave;
use LaroFilters\QueryFilter\Queries\DB\WhereHas;
use LaroFilters\QueryFilter\Queries\DB\WhereIn;
use LaroFilters\QueryFilter\Queries\DB\WhereLike;
use LaroFilters\QueryFilter\Queries\DB\WhereNull;
use LaroFilters\QueryFilter\Queries\DB\WhereNotNull;
use LaroFilters\QueryFilter\Queries\DB\WhereOr;

/**
 * Class DBBuilderQueryByCondition.
 */
class DBBuilderQueryByCondition implements MainBuilderConditionsContract
{
    const NAME = 'DbBuilder';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param $condition
     * @return string|null
     */
    public function build($condition): ?string
    {
        $builder = match ($condition) {
            'Where' => Where::class,
            'WhereBetween' => WhereBetween::class,
            'WhereByOpt' => WhereByOpt::class,
            'WhereDate' => WhereDate::class,
            'WhereDoesntHave' => WhereDoesntHave::class,
            'WhereHas' => WhereHas::class,
            'WhereIn' => WhereIn::class,
            'WhereLike' => WhereLike::class,
            'WhereOr' => WhereOr::class,
            'WhereNull' => WhereNull::class,
            'WhereNotNull' => WhereNotNull::class,
            'Special' => Special::class,
            // 'WhereCustom' => WhereCustom::class,
            default => null,
        };

        if (empty($builder) && !empty($condition)) {
            return $condition;
        }

        return $builder;
    }
}
