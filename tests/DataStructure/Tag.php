<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class Tag
{
    #[JSONField]
    public string $id;

    #[JSONField]
    public int $weight;
}
