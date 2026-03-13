<?php

declare(strict_types=1);

namespace Nauni\JSON\Field;

use RuntimeException;

use function array_key_exists;
use function gettype;
use function is_bool;
use function sprintf;

class FieldBool implements FieldInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public static function getFieldValue(array $data, string $key): ?bool
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        throw new RuntimeException(sprintf('Can not map %s to bool', gettype($value)));
    }
}
