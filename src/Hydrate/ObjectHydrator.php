<?php

declare(strict_types=1);

namespace Nauni\JSON\Hydrate;

use JsonException;
use Nauni\JSON\Attribute\JSONField;
use Nauni\JSON\Contract\JsonUnmarshaler;
use Nauni\JSON\Field\FieldInterface;
use Nauni\JSON\Json\RawJson;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_unique;
use function array_values;
use function class_exists;
use function implode;
use function is_array;
use function sprintf;

/**
 * Maps decoded assoc arrays onto objects marked with JSONField.
 */
final class ObjectHydrator
{
    private const WRAP_KEY = '_';

    /** @var ScalarRegistry */
    private $scalars;

    public function __construct(?ScalarRegistry $scalars = null)
    {
        $this->scalars = $scalars ?? new ScalarRegistry();
    }

    /**
     * @template T of object
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $class
     *
     * @return T
     */
    public function hydrate(
        array $data,
        string $class,
        bool $disallowUnknownFields = false
    ): object {
        $instance = new $class();
        $reflection = new ReflectionClass($instance);
        $mappedKeys = [];

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
            $mappedKeys[] = $jsonKey;
            $type = $property->getType();

            if (!$type instanceof ReflectionNamedType) {
                throw new RuntimeException(
                    'Only properties with named types are supported: ' . $property->getName(),
                );
            }

            $typeName = $type->getName();
            $allowsNull = $type->allowsNull();
            $hasKey = array_key_exists($jsonKey, $data);

            if (!$hasKey && null !== $meta->defaultValue) {
                $property->setValue($instance, $meta->defaultValue);
                continue;
            }

            if (!$hasKey) {
                if ($allowsNull) {
                    $property->setValue($instance, null);
                    continue;
                }
                throw new RuntimeException(sprintf(
                    'Missing required member "%s" for %s',
                    $jsonKey,
                    $property->getName(),
                ));
            }

            $raw = $data[$jsonKey];

            if ($raw === null) {
                if (!$allowsNull) {
                    throw new RuntimeException(sprintf('Null not allowed for %s', $property->getName()));
                }
                $property->setValue($instance, null);
                continue;
            }

            $property->setValue($instance, $this->valueForProperty(
                $raw,
                $typeName,
                $meta,
                $property->getName(),
                $allowsNull,
                $jsonKey,
                $disallowUnknownFields,
            ));
        }

        if ($disallowUnknownFields) {
            $this->assertKnownKeysOnly($data, $mappedKeys);
        }

        if ($instance instanceof JsonUnmarshaler) {
            $instance->afterUnmarshal($data);
        }

        return $instance;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $mappedKeys may contain duplicates
     */
    private function assertKnownKeysOnly(array $data, array $mappedKeys): void
    {
        $allowed = array_values(array_unique($mappedKeys));
        $extra = array_diff(array_keys($data), $allowed);
        if ($extra !== []) {
            throw new RuntimeException('Unknown JSON field(s): ' . implode(', ', $extra));
        }
    }

    /**
     * @param mixed $raw
     *
     * @return mixed
     */
    private function valueForProperty(
        $raw,
        string $typeName,
        JSONField $meta,
        string $propertyName,
        bool $allowsNull,
        string $jsonKey,
        bool $disallowUnknownFields
    ) {
        if ($typeName === RawJson::class) {
            try {
                return RawJson::fromDecoded($raw);
            } catch (JsonException $e) {
                throw new RuntimeException(
                    sprintf('RawJson encode failed for %s', $propertyName),
                    0,
                    $e,
                );
            }
        }

        $handlerClass = $this->scalars->handlerFor($typeName);
        if ($handlerClass !== null) {
            /** @var class-string<FieldInterface> $handlerClass */
            $value = $handlerClass::getFieldValue([$jsonKey => $raw], $jsonKey);
            if ($value === null && !$allowsNull) {
                throw new RuntimeException(sprintf('Property is not nullable: %s', $propertyName));
            }

            return $value;
        }

        if ($typeName === 'array') {
            if (!is_array($raw)) {
                throw new RuntimeException(sprintf('Expected JSON array for %s', $propertyName));
            }
            if ($meta->itemType === null) {
                throw new RuntimeException(sprintf(
                    'Array property %s requires JSONField itemType (scalar or class)',
                    $propertyName,
                ));
            }
            /** @var list<mixed> $raw */

            return $this->hydrateList($raw, $meta->itemType, $propertyName, $disallowUnknownFields);
        }

        if (class_exists($typeName)) {
            if (!is_array($raw)) {
                throw new RuntimeException(sprintf('Expected JSON object for %s', $propertyName));
            }
            /** @var array<string, mixed> $raw */

            return $this->hydrate($raw, $typeName, $disallowUnknownFields);
        }

        throw new RuntimeException(sprintf(
            'Unsupported property type "%s": %s',
            $typeName,
            $propertyName,
        ));
    }

    /**
     * @param list<mixed> $elements
     *
     * @return list<mixed>
     */
    private function hydrateList(
        array $elements,
        string $itemType,
        string $propertyName,
        bool $disallowUnknownFields
    ): array {
        if ($itemType === RawJson::class) {
            $out = [];
            foreach ($elements as $i => $el) {
                try {
                    $out[] = RawJson::fromDecoded($el);
                } catch (JsonException $e) {
                    throw new RuntimeException(sprintf('RawJson[%d] encode failed', $i), 0, $e);
                }
            }

            return $out;
        }

        $handlerClass = $this->scalars->handlerFor($itemType);
        if ($handlerClass !== null) {
            $out = [];
            foreach ($elements as $i => $el) {
                if ($el === null) {
                    throw new RuntimeException(sprintf(
                        '%s[%d]: null element not supported in typed array',
                        $propertyName,
                        $i,
                    ));
                }
                $wrap = [self::WRAP_KEY => $el];
                $out[] = $handlerClass::getFieldValue($wrap, self::WRAP_KEY);
            }

            return $out;
        }

        if (!class_exists($itemType)) {
            throw new RuntimeException(sprintf('Unknown itemType "%s" for %s', $itemType, $propertyName));
        }

        $out = [];
        foreach ($elements as $i => $el) {
            if (!is_array($el)) {
                throw new RuntimeException(sprintf('%s[%d]: expected object', $propertyName, $i));
            }
            /** @var array<string, mixed> $el */
            $out[] = $this->hydrate($el, $itemType, $disallowUnknownFields);
        }

        return $out;
    }
}
