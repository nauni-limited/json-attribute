<?php

declare(strict_types=1);

namespace Nauni\JSON;

use JsonException;
use Nauni\JSON\Decode\JsonDecoder;
use Nauni\JSON\Extract\ObjectExtractor;
use Nauni\JSON\Hydrate\ObjectHydrator;
use RuntimeException;

use function file_get_contents;
use function json_encode;
use function sprintf;

/**
 * Public API: {@see unmarshal}, {@see marshal}, {@see embed}.
 */
final class JSON
{
    private static ?ObjectHydrator $hydrator = null;

    private static ?ObjectExtractor $extractor = null;

    public static function embed(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Cannot load file: %s', $path));
        }

        return $contents;
    }

    /**
     * JSON text → object (root must be a JSON object).
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function unmarshal(
        string $data,
        string $class,
        bool $disallowUnknownFields = false
    ): object {
        $decoder = new JsonDecoder();
        $assoc = $decoder->decodeAssocObjectRoot($data);

        return self::hydrator()->hydrate($assoc, $class, $disallowUnknownFields);
    }

    /**
     * Object → JSON text (extract mapped fields, then encode).
     *
     * @throws RuntimeException on encode failure
     */
    public static function marshal(object $instance, int $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE): string
    {
        $payload = self::extractor()->extract($instance);
        try {
            $json = json_encode($payload, $flags);
            if ($json === false) {
                throw new RuntimeException('Marshal JSON encode failed');
            }

            return $json;
        } catch (JsonException $e) {
            throw new RuntimeException('Marshal JSON encode failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function hydrator(): ObjectHydrator
    {
        return self::$hydrator ??= new ObjectHydrator();
    }

    private static function extractor(): ObjectExtractor
    {
        return self::$extractor ??= new ObjectExtractor();
    }
}
