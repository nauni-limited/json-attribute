<?php

namespace Nauni\JSON;

use Nauni\JSON\Attribute\JSONField;
use Nauni\JSON\Field\FieldBool;
use Nauni\JSON\Field\FieldFloat;
use Nauni\JSON\Field\FieldInt;
use Nauni\JSON\Field\FieldInterface;
use Nauni\JSON\Field\FieldString;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

use function file_get_contents;

class JSON
{
    private const FIELD_TYPE_MAPPING = [
        'string' => FieldString::class,
        'int' => FieldInt::class,
        'float' => FieldFloat::class,
        'bool' => FieldBool::class,
    ];

    public static function embed(string $path): string
    {
        return file_get_contents($path) ?: throw new RuntimeException('Can not load file' . $path);
    }

    public static function unmarshal(string $data, string $class): object
    {
        $instance = new $class();
        $data = json_decode($data, true);

        $reflectionClass = new ReflectionClass($instance);
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(JSONField::class);
            foreach ($attributes as $attribute) {
                $jsonAttribute = $attribute->newInstance();
                if (!$jsonAttribute->field) {
                    $jsonAttribute->field = $property->getName()
                        ?? throw new \RuntimeException('Can not resolve field mapping');
                }
                $propertyType = $property->getType();

                if (!$propertyType instanceof ReflectionNamedType) {
                    throw new \RuntimeException('Only properties with named types are supported');
                }

                if (array_key_exists($propertyType->getName(), self::FIELD_TYPE_MAPPING)) {
                    /** @var FieldInterface $handlerClass */
                    $handlerClass = self::FIELD_TYPE_MAPPING[$propertyType->getName()];
                    $value = $handlerClass::getFieldValue($data, $jsonAttribute->field);
                    if ($value === null && !$propertyType->allowsNull()) {
                        if ($jsonAttribute->default) {
                            $instance->{$property->name} = $jsonAttribute->default;
                            continue;
                        }
                        throw new RuntimeException('Property is not nullable: ' . $property->name);
                    }
                    $instance->{$property->name} = $value;
                    continue;
                }

                throw new RuntimeException('Can not resolve mandatory property: ' . $property->name);
            }
        }
        return $instance;
    }
}
