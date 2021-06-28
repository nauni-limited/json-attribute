<?php

namespace Nauni\JSON\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class JSONField
{
    public function __construct(
        public ?string $field = null,
        public ?string $type = null,
        public mixed $default = null,
    ) {
    }
}
