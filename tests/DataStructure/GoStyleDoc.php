<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests\DataStructure;

use Nauni\JSON\Attribute\JSONField;
use Nauni\JSON\Contract\JsonUnmarshaler;
use Nauni\JSON\Json\RawJson;

final class GoStyleDoc implements JsonUnmarshaler
{
    #[JSONField]
    public string $name;

    /** Go json:"-" — not filled from JSON */
    #[JSONField(ignored: true)]
    public string $secret = 'default-secret';

    #[JSONField]
    public RawJson $metadata;

    #[JSONField]
    public ?RawJson $optionalRaw;

    /** @var list<RawJson> */
    #[JSONField(itemType: RawJson::class)]
    public array $events;

    public bool $afterCalled = false;

    public function afterUnmarshal(array $data): void
    {
        $this->afterCalled = true;
    }
}
