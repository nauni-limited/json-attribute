<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests;

use Nauni\JSON\JSON;
use Nauni\JSON\Tests\DataStructure\NonNamedType;
use Nauni\JSON\Tests\DataStructure\User;
use PHPUnit\Framework\TestCase;

class UnmarshalTest extends TestCase
{
    private const TD = 'tests/testdata/';

    public function testUnmarshalField(): void
    {
        $json = JSON::unmarshal(JSON::embed(self::TD . 'unmarshalScalarTestData.json'), User::class);

        $this->assertSame('John Doe', $json->name);
        $this->assertSame('John Doe', $json->person);
        $this->assertNull($json->title);
        $this->assertSame('Title', $json->defaultTitle);
        $this->assertSame(35, $json->age);
        $this->assertTrue($json->active);
        $this->assertSame(1.93, $json->height);
    }

    public function testUnmarshalNoneNamedType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only properties with named types are supported');
        JSON::unmarshal(JSON::embed(self::TD . 'nonNamedType.json'), NonNamedType::class);
    }

    public function testInvalidStringMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map integer to string');
        JSON::unmarshal(JSON::embed(self::TD . 'userInvalidString.json'), User::class);
    }

    public function testInvalidIntMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to integer');
        JSON::unmarshal(JSON::embed(self::TD . 'userInvalidInt.json'), User::class);
    }

    public function testInvalidFloatMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to float');
        JSON::unmarshal(JSON::embed(self::TD . 'userInvalidFloat.json'), User::class);
    }

    public function testInvalidBoolMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to bool');
        JSON::unmarshal(JSON::embed(self::TD . 'userInvalidBool.json'), User::class);
    }

    public function testIntZeroAndStringKey(): void
    {
        $u = JSON::unmarshal(JSON::embed(self::TD . 'userIntZero.json'), User::class);
        $this->assertSame('', $u->name);
        $this->assertSame(0, $u->age);
        $this->assertSame(0.0, $u->height);
        $this->assertFalse($u->active);
    }

    public function testInvalidJsonThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON');
        JSON::unmarshal(JSON::embed(self::TD . 'invalidJsonOpenBrace.json'), User::class);
    }

    public function testRootMustBeObject(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JSON root must be an object');
        JSON::unmarshal(JSON::embed(self::TD . 'rootArray.json'), User::class);
    }

    public function testFloatAcceptsIntegerJsonNumber(): void
    {
        $u = JSON::unmarshal(JSON::embed(self::TD . 'userFloatFromInt.json'), User::class);
        $this->assertSame(2.0, $u->height);
    }
}
