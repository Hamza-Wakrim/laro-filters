<?php

namespace LaroFilters\QueryFilter\Core\EloquentBuilder;

/**
 *
 */
interface QueryBuilderWrapperInterface
{
    /**
     * @param $builder
     */
    public function __construct($builder);

    /**
     * @return mixed
     */
    public function getBuilder();

    /**
     * @return mixed
     */
    public function getAliasListFilter();

    /**
     * @return mixed
     */
    public function getModel();
}
