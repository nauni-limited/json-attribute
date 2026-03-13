<?php

declare(strict_types=1);

namespace Nauni\JSON\Decode;

use JsonException;
use RuntimeException;

use function is_object;
use function json_decode;

/**
 * Decodes JSON text; root must be an object (Go-style document).
 */
final class JsonDecoder
{
    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException Invalid JSON or root not an object
     */
    public function decodeAssocObjectRoot(string $json): array
    {
        try {
            $root = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON: ' . $e->getMessage(), 0, $e);
        }

        if (!is_object($root)) {
            throw new RuntimeException('JSON root must be an object');
        }

        try {
            $assoc = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON: ' . $e->getMessage(), 0, $e);
        }

        /** @var array<string, mixed> $assoc */
        return $assoc;
    }
}
