<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests;

use Nauni\JSON\JSON;
use Nauni\JSON\Tests\DataStructure\ComplexDocument;
use Nauni\JSON\Tests\DataStructure\NullableNested;
use Nauni\JSON\Tests\DataStructure\User;
use PHPUnit\Framework\TestCase;

/** RFC 8259-style JSON: objects, arrays, scalars, null, nesting. */
class ComplexJsonTest extends TestCase
{
    private const TD = 'tests/testdata/';

    public function testNestedObjectAndArrays(): void
    {
        $doc = JSON::unmarshal(JSON::embed(self::TD . 'complexNestedDocument.json'), ComplexDocument::class);

        $this->assertSame('doc-1', $doc->id);
        $this->assertSame('1 Main', $doc->address->street);
        $this->assertSame('London', $doc->address->city);
        $this->assertNull($doc->address->region);
        $this->assertSame([10, 0, -3], $doc->scores);
        $this->assertSame(['a', '', 'café'], $doc->labels);
        $this->assertCount(2, $doc->tags);
        $this->assertSame('t1', $doc->tags[0]->id);
        $this->assertSame(0, $doc->tags[1]->weight);
        $this->assertSame('X', $doc->firstLine->sku);
        $this->assertSame(2, $doc->firstLine->qty);
        $this->assertSame(1, $doc->lines[0]->qty);
        $this->assertSame(2.0, $doc->lines[1]->price);
        $this->assertTrue($doc->enabled);
        $this->assertFalse($doc->flagDefaultFalse);
        $this->assertSame(0, $doc->zeroDefault);
    }

    public function testUnicodeEscapesAndLiterals(): void
    {
        $doc = JSON::unmarshal(JSON::embed(self::TD . 'complexMin.json'), ComplexDocument::class);
        $this->assertSame('A', $doc->address->street);
        $this->assertSame('π', $doc->address->city);
        $this->assertSame(["\n"], $doc->labels);
    }

    public function testNullableNestedOmittedUsesNull(): void
    {
        $n = JSON::unmarshal(JSON::embed(self::TD . 'nullableNestedNameOnly.json'), NullableNested::class);
        $this->assertSame('n', $n->name);
        $this->assertNull($n->extra);
    }

    public function testNullableNestedPresent(): void
    {
        $n = JSON::unmarshal(JSON::embed(self::TD . 'nullableNestedWithExtra.json'), NullableNested::class);
        $this->assertNotNull($n->extra);
        $this->assertSame('s', $n->extra->street);
    }

    public function testEmptyArrays(): void
    {
        $doc = JSON::unmarshal(JSON::embed(self::TD . 'complexEmpty.json'), ComplexDocument::class);
        $this->assertSame([], $doc->scores);
        $this->assertSame([], $doc->tags);
    }

    public function testArrayElementWrongType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to integer');
        JSON::unmarshal(JSON::embed(self::TD . 'complexScoresWrongType.json'), ComplexDocument::class);
    }

    public function testNestedObjectExpectedButGotScalar(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected JSON object');
        JSON::unmarshal(JSON::embed(self::TD . 'complexAddressScalar.json'), ComplexDocument::class);
    }

    public function testMissingRequiredMember(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing required member');
        JSON::unmarshal(JSON::embed(self::TD . 'complexMissingRequired.json'), ComplexDocument::class);
    }

    public function testExplicitNullOnNonNullableNested(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Null not allowed');
        JSON::unmarshal(JSON::embed(self::TD . 'complexAddressNull.json'), ComplexDocument::class);
    }

    public function testLargeInteger(): void
    {
        $u = JSON::unmarshal(JSON::embed(self::TD . 'userLargeInt.json'), User::class);
        $this->assertSame(9007199254740991, $u->age);
    }

    public function testScientificNotationNumber(): void
    {
        $u = JSON::unmarshal(JSON::embed(self::TD . 'userScientific.json'), User::class);
        $this->assertSame(100.0, $u->height);
    }
}
