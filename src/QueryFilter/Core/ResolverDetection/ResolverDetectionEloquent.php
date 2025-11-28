<?php

namespace LaroFilters\QueryFilter\Core\ResolverDetection;

use LaroFilters\QueryFilter\Detection\Contract\DetectorFactoryContract;
use LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use LaroFilters\QueryFilter\Queries\BaseClause;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ResolverDetectionEloquent extends ResolverDetections
{

    /**
     * ResolverDetections constructor.
     * @param $builder
     * @param array $request
     * @param \LaroFilters\QueryFilter\Detection\Contract\DetectorFactoryContract $detector_factory
     * @param \LaroFilters\QueryFilter\Detection\Contract\MainBuilderConditionsContract $main_builder_conditions_contract
     */
    public function __construct($builder, array $request, DetectorFactoryContract $detector_factory, MainBuilderConditionsContract $main_builder_conditions_contract)
    {
        $this->builder = $builder;
        $this->request = $request;
        $this->detector_factory = $detector_factory;

        $this->main_builder_conditions = $main_builder_conditions_contract;
    }
    /**
     * @return array
     */
    public function getFiltersDetection(): array
    {
        try {
            if (empty($this->request) || !is_array($this->request)) {
                Log::warning('LaroFilters: Empty or invalid request in getFiltersDetection()');
                return [];
            }

            $model = $this->builder->getModel();

            if (!$model) {
                Log::error('LaroFilters: Model is null in getFiltersDetection()');
                throw new LaroFiltersException("Builder model is null.", 4);
            }

            $filter_detections = collect($this->request)->map(function ($values, $filter) use ($model) {
                try {
                    return $this->resolve($filter, $values, $model);
                } catch (LaroFiltersException $e) {
                    Log::error('LaroFilters: Error resolving filter detection', [
                        'filter' => $filter,
                        'values' => $values,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                } catch (\Exception $e) {
                    Log::error('LaroFilters: Unexpected error resolving filter detection', [
                        'filter' => $filter,
                        'values' => $values,
                        'error' => $e->getMessage(),
                    ]);
                    // Return null to filter out failed detections
                    return null;
                }
            })->reverse()->filter(function ($item) {
                return $item instanceof BaseClause;
            })->toArray();

            $out = Arr::isAssoc($filter_detections) ? $filter_detections : [];

            return $out;
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in getFiltersDetection()', [
                'request' => $this->request ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in getFiltersDetection(): " . $e->getMessage(), 4);
        }
    }

    /**
     * @param $filterName
     * @param $values
     * @param $model
     *
     * @return Application|mixed
     * @throws ReflectionException
     *
     */
    protected function resolve($filterName, $values, $model)
    {
        try {
            if (empty($filterName) || !is_string($filterName)) {
                Log::error('LaroFilters: Invalid filterName in resolve()', [
                    'filterName' => $filterName,
                ]);
                throw new LaroFiltersException("Filter name must be a non-empty string.", 4);
            }

            try {
                $detectedConditions = $this->detector_factory->buildDetections($filterName, $values, $model);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error building detections in resolve()', [
                    'filterName' => $filterName,
                    'values' => $values,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error detecting conditions for filter '{$filterName}': " . $e->getMessage(), 4);
            }

            try {
                $builderDriver = $this->main_builder_conditions->build($detectedConditions);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error building query driver in resolve()', [
                    'filterName' => $filterName,
                    'detectedConditions' => $detectedConditions,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error building query driver for filter '{$filterName}': " . $e->getMessage(), 4);
            }

            if (empty($builderDriver)) {
                Log::warning('LaroFilters: Empty builder driver in resolve()', [
                    'filterName' => $filterName,
                    'detectedConditions' => $detectedConditions,
                ]);
                return null;
            }

            try {
                return app($builderDriver, ['filter' => $filterName, 'values' => $values]);
            } catch (\Exception $e) {
                Log::error('LaroFilters: Error instantiating query class in resolve()', [
                    'filterName' => $filterName,
                    'builderDriver' => $builderDriver,
                    'error' => $e->getMessage(),
                ]);
                throw new LaroFiltersException("Error instantiating query class '{$builderDriver}': " . $e->getMessage(), 4);
            }
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in resolve()', [
                'filterName' => $filterName ?? null,
                'values' => $values ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new LaroFiltersException("Unexpected error in resolve(): " . $e->getMessage(), 4);
        }
    }
}
