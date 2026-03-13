<?php

declare(strict_types=1);

namespace Nauni\JSON\Attribute;

use Attribute;

/**
 * Mirrors common Go struct tags: json:"name", json:"-", omitempty (marshal TBD).
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JSONField
{
    public function __construct(
        public ?string $field = null,
        public ?string $itemType = null,
        /** @var mixed If not null, used when JSON member absent */
        public $defaultValue = null,
        public bool $ignored = false,
        public bool $omitempty = false,
    ) {
    }
}
