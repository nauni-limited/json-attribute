<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class OrderLine
{
    #[JSONField]
    public string $sku;

    #[JSONField]
    public int $qty;

    #[JSONField]
    public float $price;
}
