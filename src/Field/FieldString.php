<?php

namespace Nauni\JSON\Field;

use function gettype;
use function is_string;
use function sprintf;

class FieldString implements FieldInterface
{
    public static function getFieldValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (!$value || is_string($value)) {
            return $value;
        }

        throw new \RuntimeException(sprintf('Can not map %s to string', gettype($value)));
    }
}
