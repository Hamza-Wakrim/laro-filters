<?php

namespace LaroFilters\QueryFilter\Detection\DetectionFactory;

use LaroFilters\QueryFilter\Detection\Contract\DetectorDbFactoryContract;
use LaroFilters\QueryFilter\Detection\Detector\DetectorConditionDbCondition;

/**
 * Class DetectionDbFactory.
 */
class DetectionDbFactory implements DetectorDbFactoryContract
{
    /**
     * DetectionDbFactory constructor.
     *
     * @param array $detections
     */
    public function __construct(public array $detections)
    {
    }

    /**
     * @param $field
     * @param $params
     *
     * @return string|null
     */
    public function buildDetections($field, $params): ?string
    {
        /** @var DetectorConditionDbCondition $detect */
        $detect = app(DetectorConditionDbCondition::class, ['detector' => $this->detections]);

        $method = $detect->detect($field, $params);

        return $method;
    }
}
