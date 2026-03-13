<?php

declare(strict_types=1);

namespace Nauni\JSON\Field;

interface FieldInterface
{
    /**
     * Read and validate a value from decoded JSON (assoc array). Missing key => null.
     *
     * @param array<string, mixed> $data
     *
     * @return mixed
     */
    public static function getFieldValue(array $data, string $key);
}
