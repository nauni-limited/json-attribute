<?php

namespace Nauni\JSON\Field;

use function is_float;
use function sprintf;

class FieldFloat
{
    public static function getFieldValue(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        if (!$value || is_float($value)) {
            return $value;
        }

        throw new \RuntimeException(sprintf('Can not map %s to float', gettype($value)));
    }
}
