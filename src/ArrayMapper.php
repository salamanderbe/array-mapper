<?php

namespace Salamander\ArrayMapper;

class ArrayMapper
{
    /**
     * @param array|string $mapping
     * @return int|float|string|array|null
     */
    public function map(object $object, $mapping)
    {
        return $this->handleMap($object, $mapping, true);
    }

    /**
     * @param array|string $mapping
     * @return int|float|string|array|null
     */
    protected function handleMap(object $object, $mapping, bool $mergeArrays)
    {
        if (!is_array($mapping)) {
            return $this->mapSingleValue($object, $mapping, $mergeArrays);
        }

        $value = [];
        foreach ($mapping as $mapKey => $mapValue) {
            if (substr($mapKey, -2) === '.*') {
                $value[substr($mapKey, 0, -2)] = $this->mapArrayValue($object, $mapValue);
                continue;
            }

            $mappedResponse = $this->handleMap($object, $mapValue, $mergeArrays);
            if ($mappedResponse !== null) {
                $value[$mapKey] = $mappedResponse;
            }
        }
        return count($value) > 0 ? $value : null;
    }

    /** @return int|float|string|array|null */
    protected function mapSingleValue(object $object, string $mapping, bool $mergeArrays)
    {
        if ($mapping[0] === '#') {
            $mapping = substr($mapping, 1);
            if ($mapping[0] !== '#') {
                return $mapping;
            }
        }
        $sbKeys = $this->mapArrayKeys(explode('.', $mapping));
        return $this->mapValue($object, $sbKeys, $mergeArrays);
    }

    protected function mapArrayValue(object $object, array $mapping): array
    {
        if (array_keys($mapping) !== range(0, count($mapping) - 1)) {
            $mapping = [$mapping];
        }

        $allNested = [];
        foreach ($mapping as $nestedValue) {
            $nested = [];
            $childResponses = $this->handleMap($object, $nestedValue, false);
            $length = count(array_values($childResponses)[0] ?? []);

            foreach ($childResponses as $field => $responseValues) {
                if (!is_array($responseValues)) {
                    $responseValues = array_pad([], $length, $responseValues);
                }
                foreach ($responseValues as $index => $responseValue) {
                    $child = $nested[$index] ?? [];
                    $child[$field] = $responseValue;
                    $nested[$index] = $child;
                }
            }
            $allNested = array_merge($allNested, $nested);
        }

        return $allNested;
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

    /** @return int|float|string|array|null */
    protected function mapValue(object $object, array $keys, bool $mergeArrays)
    {
        $value = $object;
        foreach ($keys as $key) {
            if (is_array($key)) {
                $arrayValue = [];
                foreach ($value as $nestedValue) {
                    $mappedValue = $this->mapValue($nestedValue, $key, true);
                    if (is_array($mappedValue) && $mergeArrays) {
                        $arrayValue = array_merge($arrayValue, $mappedValue);
                        continue;
                    }
                    $arrayValue[] = $this->mapValue($nestedValue, $key, true);
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
