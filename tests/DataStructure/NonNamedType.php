<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class NonNamedType
{
    /** Intentionally untyped for test (getType() not ReflectionNamedType). */
    #[JSONField]
    public $name;
}
