<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

class User
{
    #[JSONField]
    public string $name;

    #[JSONField(field: 'name')]
    public string $person;

    #[JSONField]
    public ?string $title;

    #[JSONField(field: 'title', defaultValue: 'Title')]
    public string $defaultTitle;

    #[JSONField]
    public int $age;

    #[JSONField]
    public float $height;

    #[JSONField]
    public bool $active;
}
