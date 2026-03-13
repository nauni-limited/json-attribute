<?php

declare(strict_types=1);

namespace Nauni\JSON\Contract;

/**
 * Optional hook like Go's json.Unmarshaler (per-type decode).
 * Invoked after the hydrator builds the instance when the class implements this interface.
 */
interface JsonUnmarshaler
{
    /**
     * @param array<string, mixed> $data Full decoded JSON object for this value (assoc)
     */
    public function afterUnmarshal(array $data): void;
}
