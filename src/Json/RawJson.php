<?php

declare(strict_types=1);

namespace Nauni\JSON\Json;

use JsonException;
use Stringable;

use function json_decode;
use function json_encode;

/**
 * Holds a JSON value as text — same role as Go's json.RawMessage.
 */
final class RawJson implements Stringable
{
    public function __construct(
        public string $value,
    ) {
    }

    /**
     * @param mixed $decoded
     *
     * @throws JsonException
     */
    public static function fromDecoded($decoded): self
    {
        return new self(json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @throws JsonException
     *
     * @return mixed
     */
    public function decode()
    {
        return json_decode($this->value, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     *
     * @return mixed
     */
    public function decodeObject()
    {
        return json_decode($this->value, false, 512, JSON_THROW_ON_ERROR);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
