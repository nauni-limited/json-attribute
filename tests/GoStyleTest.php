<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests;

use Nauni\JSON\JSON;
use Nauni\JSON\Tests\DataStructure\GoStyleDoc;
use PHPUnit\Framework\TestCase;

class GoStyleTest extends TestCase
{
    private const TD = 'tests/testdata/';

    public function testRawJsonPreservesStructure(): void
    {
        $doc = JSON::unmarshal(JSON::embed(self::TD . 'goStyleRawJsonFull.json'), GoStyleDoc::class);

        $this->assertSame('a', $doc->name);
        $this->assertSame('default-secret', $doc->secret);
        $meta = $doc->metadata->decode();
        $this->assertIsArray($meta);
        $this->assertSame(1, $meta['x']);
        $this->assertSame([true, null], $meta['nested']);
        $this->assertNull($doc->optionalRaw);
        $this->assertCount(3, $doc->events);
        $this->assertSame(['k' => 'v'], $doc->events[0]->decode());
        $this->assertSame(42, $doc->events[1]->decode());
        $this->assertSame('s', $doc->events[2]->decode());
        $this->assertTrue($doc->afterCalled);
    }

    public function testRawJsonScalar(): void
    {
        $doc = JSON::unmarshal(JSON::embed(self::TD . 'goStyleRawJsonScalar.json'), GoStyleDoc::class);
        $this->assertFalse($doc->metadata->decode());
    }

    public function testDisallowUnknownFields(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown JSON field(s): extra');
        JSON::unmarshal(
            JSON::embed(self::TD . 'goStyleUnknownExtra.json'),
            GoStyleDoc::class,
            disallowUnknownFields: true,
        );
    }

    public function testDisallowUnknownFieldsPassesWhenOnlyMapped(): void
    {
        $doc = JSON::unmarshal(
            JSON::embed(self::TD . 'goStyleMappedOnly.json'),
            GoStyleDoc::class,
            disallowUnknownFields: true,
        );
        $this->assertSame('c', $doc->name);
    }

    public function testIgnoredDoesNotConsumeUnknownCheck(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('secret');
        JSON::unmarshal(
            JSON::embed(self::TD . 'goStyleSecretUnknown.json'),
            GoStyleDoc::class,
            disallowUnknownFields: true,
        );
    }
}
