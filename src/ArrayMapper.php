<?php

namespace Salamander\ArrayMapper;

class ArrayMapper
{
    /** @param array|string $mapping */
    public function map(object $object, $mapping, bool $mergeArrays = true)
    {
        if (!is_array($mapping)) {
            $sbKeys = $this->mapArrayKeys(explode('.', $mapping));
            return $this->mapValue($object, $sbKeys, $mergeArrays);
        }

        $value = [];
        foreach ($mapping as $mapKey => $mapValue) {
            if (substr($mapKey, -2) === '.*') {
                if (array_keys($mapValue) !== range(0, count($mapValue) - 1)) {
                    $mapValue = [$mapValue];
                }

                $allNested = [];
                foreach ($mapValue as $nestedValue) {
                    $nested = [];
                    $childResponses = $this->map($object, $nestedValue, false);
                    foreach ($childResponses as $field => $responseValues) {
                        foreach ($responseValues as $index => $responseValue) {
                            $child = $nested[$index] ?? [];
                            $child[$field] = $responseValue;
                            $nested[$index] = $child;
                        }
                    }
                    $allNested = array_merge($allNested, $nested);
                }

                $value[substr($mapKey, 0, -2)] = $allNested;
                continue;
            }

            $mappedResponse = $this->map($object, $mapValue, $mergeArrays);
            if ($mappedResponse !== null) {
                $value[$mapKey] = $mappedResponse;
            }
        }
        return count($value) > 0 ? $value : null;
    }

    protected function mapArrayKeys(array $keys): array
    {
        $mappedKeys = [];
        foreach ($keys as $index => $key) {
            if ($key !== '*') {
                $mappedKeys[] = $key;
                continue;
            }
            $mappedKeys[] = $this->mapArrayKeys(array_slice($keys, $index + 1));
        }
        return $mappedKeys;
    }

    protected function mapValue(object $object, array $keys, bool $mergeArrays = true)
    {
        $value = $object;
        foreach ($keys as $key) {
            if (is_array($key)) {
                $arrayValue = [];
                foreach ($value as $nestedValue) {
                    $mappedValue = $this->mapValue($nestedValue, $key);
                    if (is_array($mappedValue) && $mergeArrays) {
                        $arrayValue = array_merge($arrayValue, $mappedValue);
                        continue;
                    }
                    $arrayValue[] = $this->mapValue($nestedValue, $key);
                }
                return $arrayValue;
            }

            if (!isset($value->{$key})) {
                return null;
            }
            $value = $value->{$key};
        }
        return $value;
    }
}
