<?php

namespace Nauni\JSON\Field;

use function is_bool;
use function sprintf;

class FieldBool implements FieldInterface
{
    public static function getFieldValue(array $data, string $key): ?bool
    {
        $value = $data[$key] ?? null;

        if (!$value || is_bool($value)) {
            return $value;
        }

        throw new \RuntimeException(sprintf('Can not map %s to bool', gettype($value)));
    }
}
