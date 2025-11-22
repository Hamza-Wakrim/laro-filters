<?php

namespace LaroFilters\QueryFilter\Core\ResolverDetection;

use LaroFilters\QueryFilter\Core\FilterBuilder\MainQueryFilterBuilder;
use LaroFilters\QueryFilter\Core\ReflectionException;
use LaroFilters\QueryFilter\Detection\Contract\DetectorFactoryContract;
use LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract;
use LaroFilters\QueryFilter\Detection\DetectionFactory\DetectionDbFactory;
use Illuminate\Pipeline\Pipeline;

/**
 * Class ResolverDetections
 * @package LaroFilters\QueryFilter\Core
 */
abstract class ResolverDetections
{
    /**
     * @var
     */
    protected $builder;
    /**
     * @var array
     */
    protected array $request;
    /**
     * @var \LaroFilters\QueryFilter\Detection\Contract\DetectorFactoryContract
     */
    protected DetectorFactoryContract $detector_factory;
    protected DetectionDbFactory $detector_db_factory;

    protected MainBuilderConditionsContract $main_builder_conditions;


    /**
     * @return mixed
     * @see \LaroFilters\QueryFilter\Core\FilterBuilder\MainQueryFilterBuilder
     */
    public function getResolverOut()
    {
        $filter_detections = $this->getFiltersDetection();

        $out = app(Pipeline::class)
            ->send($this->builder)
            ->through($filter_detections)
            ->thenReturn();

        return $out;
    }

    /**
     * @return array
     */
    abstract public function getFiltersDetection(): array;
}
