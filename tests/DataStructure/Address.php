<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class Address
{
    #[JSONField]
    public string $street;

    #[JSONField]
    public string $city;

    #[JSONField]
    public ?string $region;
}
