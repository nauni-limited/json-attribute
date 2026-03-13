<?php

declare(strict_types=1);

namespace Nauni\JSON\Extract;

use JsonException;
use Nauni\JSON\Attribute\JSONField;
use Nauni\JSON\Json\RawJson;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

use function class_exists;
use function is_array;
use function json_decode;
use function sprintf;

/**
 * Extracts a JSON-serializable array tree from a DTO (#[JSONField]).
 *
 * @internal
 */
final class ObjectExtractor
{
    /**
     * @return array<string, mixed>
     */
    public function extract(object $instance): array
    {
        $out = [];
        $reflection = new ReflectionClass($instance);

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(JSONField::class);
            if ($attrs === []) {
                continue;
            }
            $meta = $attrs[0]->newInstance();
            if ($meta->ignored) {
                continue;
            }

            $jsonKey = $meta->field ?? $property->getName();
            $type = $property->getType();
            if (!$type instanceof ReflectionNamedType) {
                throw new RuntimeException(
                    'Extract: only named types supported: ' . $property->getName(),
                );
            }
            $typeName = $type->getName();
            $value = $property->getValue($instance);

            if ($meta->omitempty && $this->isEmptyForOmit($value)) {
                continue;
            }

            $out[$jsonKey] = $this->encodeValue($value, $typeName, $meta, $property->getName());
        }

        return $out;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function encodeValue($value, string $typeName, JSONField $meta, string $propertyName)
    {
        if ($value === null) {
            return null;
        }

        if ($typeName === RawJson::class) {
            /** @var RawJson $value */
            try {
                return json_decode($value->value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException(
                    sprintf('Extract RawJson failed for %s', $propertyName),
                    0,
                    $e,
                );
            }
        }

        if ($typeName === 'string' || $typeName === 'int' || $typeName === 'float' || $typeName === 'bool') {
            return $value;
        }

        if ($typeName === 'array') {
            if ($meta->itemType === null) {
                throw new RuntimeException(sprintf(
                    'Extract: array %s needs itemType',
                    $propertyName,
                ));
            }
            if (!is_array($value)) {
                throw new RuntimeException(sprintf('Extract: %s must be array', $propertyName));
            }

            return $this->extractList($value, $meta->itemType, $propertyName);
        }

        if (class_exists($typeName) && is_object($value)) {
            return $this->extract($value);
        }

        throw new RuntimeException(sprintf(
            'Extract: unsupported type %s for %s',
            $typeName,
            $propertyName,
        ));
    }

    /**
     * @param list<mixed> $list
     *
     * @return list<mixed>
     */
    private function extractList(array $list, string $itemType, string $propertyName): array
    {
        $out = [];
        if ($itemType === RawJson::class) {
            foreach ($list as $item) {
                /** @var RawJson $item */
                try {
                    $out[] = json_decode($item->value, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    throw new RuntimeException(sprintf('Extract RawJson[] %s', $propertyName), 0, $e);
                }
            }

            return $out;
        }

        if (in_array($itemType, ['string', 'int', 'float', 'bool'], true)) {
            return array_values($list);
        }

        if (!class_exists($itemType)) {
            throw new RuntimeException(sprintf('Extract: unknown itemType %s', $itemType));
        }

        foreach ($list as $i => $item) {
            if (!is_object($item)) {
                throw new RuntimeException(sprintf('%s[%d]: expected object', $propertyName, $i));
            }
            $out[] = $this->extract($item);
        }

        return $out;
    }

    /**
     * @param mixed $value
     */
    private function isEmptyForOmit($value): bool
    {
        if ($value === null || $value === false || $value === 0 || $value === 0.0 || $value === '') {
            return true;
        }
        if (is_array($value) && $value === []) {
            return true;
        }

        return false;
    }
}
