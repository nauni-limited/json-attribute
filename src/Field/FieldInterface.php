<?php

namespace Nauni\JSON\Field;

interface FieldInterface
{
    public static function getFieldValue(array $data, string $key): mixed;
}
