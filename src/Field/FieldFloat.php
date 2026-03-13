<?php

declare(strict_types=1);

namespace Nauni\JSON\Field;

use RuntimeException;

use function array_key_exists;
use function gettype;
use function is_float;
use function is_int;
use function sprintf;

class FieldFloat implements FieldInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public static function getFieldValue(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        throw new RuntimeException(sprintf('Can not map %s to float', gettype($value)));
    }
}
