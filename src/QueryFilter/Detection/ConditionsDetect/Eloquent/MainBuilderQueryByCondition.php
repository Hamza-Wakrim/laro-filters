<?php

namespace LaroFilters\QueryFilter\Detection\ConditionsDetect\Eloquent;

use LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract;
use LaroFilters\QueryFilter\Queries\Eloquent\Special;
use LaroFilters\QueryFilter\Queries\Eloquent\Where;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereBetween;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereByOpt;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereCustom;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereDate;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereDoesntHave;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereHas;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereIn;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereLike;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereNull;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereNotNull;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereOr;

/**
 * Class MainBuilderQueryByCondition.
 */
class MainBuilderQueryByCondition implements MainBuilderConditionsContract
{
    const NAME = 'EloquentBuilder';

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
            'WhereHas' => WhereHas::class,
            'WhereIn' => WhereIn::class,
            'WhereLike' => WhereLike::class,
            'WhereOr' => WhereOr::class,
            'WhereDoesntHave' => WhereDoesntHave::class,
            'WhereNull' => WhereNull::class,
            'WhereNotNull' => WhereNotNull::class,
            'Special' => Special::class,
            'WhereCustom' => WhereCustom::class,
            default => null,
        };

        if (empty($builder) && !empty($condition)) {
            return $condition;
        }

        return $builder;
    }
}
