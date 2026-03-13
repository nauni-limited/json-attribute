<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;

/**
 * DTO for nested object + arrays (RFC 8259 object / array values).
 */
class ComplexDocument
{
    #[JSONField]
    public string $id;

    #[JSONField]
    public Address $address;

    /** @var list<int> */
    #[JSONField(itemType: 'int')]
    public array $scores;

    /** @var list<string> */
    #[JSONField(itemType: 'string')]
    public array $labels;

    /** @var list<Tag> */
    #[JSONField(itemType: Tag::class)]
    public array $tags;

    #[JSONField]
    public OrderLine $firstLine;

    /** @var list<OrderLine> */
    #[JSONField(itemType: OrderLine::class)]
    public array $lines;

    #[JSONField]
    public bool $enabled;

    #[JSONField(defaultValue: false)]
    public bool $flagDefaultFalse;

    #[JSONField(defaultValue: 0)]
    public int $zeroDefault;
}
