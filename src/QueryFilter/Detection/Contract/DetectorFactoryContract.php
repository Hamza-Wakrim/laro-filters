<?php

namespace LaroFilters\QueryFilter\Detection\Contract;

/**
 * Interface Detector.
 */
interface DetectorFactoryContract
{
    /**
     * @param string $field
     * @param $params
     * @return string|null
     */
    public function buildDetections(string $field, $params): ?string;
}
