<?php

namespace Nauni\JSON\Field;

use function is_int;
use function sprintf;

class FieldInt
{
    public static function getFieldValue(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        if (!$value || is_int($value)) {
            return $value;
        }

        throw new \RuntimeException(sprintf('Can not map %s to integer', gettype($value)));
    }
}
