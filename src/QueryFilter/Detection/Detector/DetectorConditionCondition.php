<?php

namespace LaroFilters\QueryFilter\Detection\Detector;

use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\SpecialCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereDoesntHaveCondition;
use LaroFilters\QueryFilter\Detection\ConditionsDetect\TypeQueryConditions\WhereOrCondition;
use LaroFilters\QueryFilter\Detection\Contract\DefaultConditionsContract;
use LaroFilters\QueryFilter\Detection\Contract\DetectorConditionContract;
use LaroFilters\QueryFilter\Exceptions\LaroFiltersException;
use LaroFilters\QueryFilter\Queries\Eloquent\WhereCustom;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class DetectorConditionCondition.
 */
class DetectorConditionCondition implements DetectorConditionContract
{
    protected string $errorExceptionWhileList = "You must set %s in whiteListFilter in %s
         or create an override method with name %s or call ignoreRequest method for ignore %s.";
    /**
     * @var \Illuminate\Support\Collection
     */
    private \Illuminate\Support\Collection $detector;

    /**
     * DetectorConditions constructor.
     *
     * @param array $detector
     */
    public function __construct(array $detector)
    {
        $detector_collect = collect($detector);

        $detector_collect->map(function ($detector_obj) {
            if (!empty($detector_obj)) {
                $reflect = new \ReflectionClass($detector_obj);
                if ($reflect->implementsInterface(DefaultConditionsContract::class)) {
                    return $detector_obj;
                }
            }
        })->toArray();

        $this->setDetector($detector_collect);
    }

    /**
     * @param \Illuminate\Support\Collection $detector
     */
    public function setDetector(\Illuminate\Support\Collection $detector): void
    {
        $this->detector = $detector;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDetector(): \Illuminate\Support\Collection
    {
        return $this->detector;
    }

    /**
     * @param string $field
     * @param $params
     * @param null $getWhiteListFilter
     * @param bool $hasOverrideMethod
     * @param $className
     * @return string|null
     */
    public function detect(string $field, $params, $getWhiteListFilter = null, bool $hasOverrideMethod = false, $className = null): ?string
    {
        try {
            if (empty($field) || !is_string($field)) {
                Log::error('LaroFilters: Invalid field in DetectorConditionCondition::detect()', [
                    'field' => $field,
                ]);
                return null;
            }

            $out = $this->getDetector()->map(function ($item) use ($field, $params, $getWhiteListFilter, $hasOverrideMethod, $className) {
                try {
                    if ($this->handelListFields($field, $getWhiteListFilter, $hasOverrideMethod, $className)) {
                        if ($hasOverrideMethod) {
                            $query = WhereCustom::class;
                        } else {
                            try {
                                /** @see DefaultConditionsContract::detect() */
                                $query = $item::detect($field, $params);
                            } catch (\Exception $e) {
                                Log::error('LaroFilters: Error in condition detection', [
                                    'item' => $item,
                                    'field' => $field,
                                    'error' => $e->getMessage(),
                                ]);
                                return null;
                            }
                        }

                        if (!empty($query)) {
                            return $query;
                        }
                    }
                } catch (LaroFiltersException $e) {
                    // Re-throw whitelist exceptions
                    throw $e;
                } catch (\Exception $e) {
                    Log::warning('LaroFilters: Error in detector map function', [
                        'item' => $item ?? null,
                        'field' => $field,
                        'error' => $e->getMessage(),
                    ]);
                    return null;
                }
            })->filter();

            return $out->first();
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in DetectorConditionCondition::detect()', [
                'field' => $field ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * @param string $field
     * @param array|null $list_white_filter_model
     * @param bool $has_override_method
     * @param $model_class
     *
     * @return bool
     * @throws Exception
     *
     */
    private function handelListFields(string $field, ?array $list_white_filter_model, bool $has_override_method, $model_class): bool
    {
        try {
            if ($this->checkSetWhiteListFields($field, $list_white_filter_model) || $this->checkReservedParam($field) || $has_override_method) {
                return true;
            }

            Log::error('LaroFilters: Field not in whitelist', [
                'field' => $field,
                'model_class' => $model_class,
                'whitelist' => $list_white_filter_model,
            ]);
            throw new LaroFiltersException(sprintf($this->errorExceptionWhileList, $field, $model_class, $field, $field), 1);
        } catch (LaroFiltersException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LaroFilters: Unexpected error in handelListFields()', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);
            throw new LaroFiltersException("Error checking whitelist for field '{$field}': " . $e->getMessage(), 1);
        }
    }

    /**
     * @param string $field
     * @param array|null $query
     *
     * @return bool
     */
    private function checkSetWhiteListFields(string $field, ?array $query): bool
    {
        if (in_array($field, $query) || (!empty($query[0]) && $query[0] == '*')) {
            return true;
        }

        return false;
    }

    /**
     * @param string $field
     * @return bool
     */
    private function checkReservedParam(string $field): bool
    {
        return ($field == SpecialCondition::PARAM_NAME || $field == WhereOrCondition::PARAM_NAME || $field == WhereDoesntHaveCondition::PARAM_NAME);
    }
}
