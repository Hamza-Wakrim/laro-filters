<?php

namespace LaroFilters\QueryFilter\Core\DbBuilder;

/**
 *
 */
interface DbBuilderWrapperInterface
{
    /**
     * @param $builder
     */
    public function __construct($builder);

    /**
     * @return mixed
     */
    public function getBuilder();

}
