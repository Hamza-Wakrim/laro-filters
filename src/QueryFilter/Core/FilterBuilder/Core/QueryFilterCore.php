<?php

namespace LaroFilters\QueryFilter\Core\FilterBuilder\Core;

use LaroFilters\QueryFilter\Detection\Contract\DetectorFactoryContract;
use LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract;

/**
 *
 */
interface QueryFilterCore
{
    /**
     * @param array $defaultDetections
     * @param array|null $injectedDetections
     * @param \LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract $mainBuilderConditions
     */
    public function __construct(array $defaultDetections, array $injectedDetections = null, MainBuilderConditionsContract $mainBuilderConditions);

    /**
     * @return mixed
     */
    public function getDetectorFactory(): DetectorFactoryContract;

    /**
     * @param $default_detect
     * @return void
     */
    public function setDefaultDetect($default_detect): void;

    /**
     * @return mixed
     */
    public function getDefaultDetect(): array;

    /**
     * @param array $detections
     * @return void
     */
    public function setDetections(array $detections): void;

    /**
     * @param \LaroFilters\QueryFilter\Detection\DetectionFactory\DetectionEloquentFactory $detect_factory
     * @return void
     */
    public function setDetectFactory(DetectorFactoryContract $detect_factory): void;

    /**
     * @return \LaroFilters\QueryFilter\Detection\DetectionFactory\DetectionEloquentFactory
     */
    public function getDetectFactory(): DetectorFactoryContract;

    /**
     * @return mixed
     */
    public function getDetections(): array;

    /**
     * @param $injected_detections
     * @return void
     */
    public function setInjectedDetections($injected_detections): void;

    /**
     * @return mixed
     */
    public function getInjectedDetections(): mixed;
}
