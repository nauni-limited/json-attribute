<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class NullableNested
{
    #[JSONField]
    public string $name;

    #[JSONField]
    public ?Address $extra;
}
