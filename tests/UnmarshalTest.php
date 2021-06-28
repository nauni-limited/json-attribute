<?php

namespace Nauni\JSON\Tests;

use Nauni\JSON\JSON;
use Nauni\JSON\Tests\DataStructure\NonNamedType;
use Nauni\JSON\Tests\DataStructure\User;
use PHPUnit\Framework\TestCase;

class UnmarshalTest extends TestCase
{
    public function testUnmarshalField(): void
    {
        $json = JSON::unmarshal(
            data: JSON::embed('tests/testdata/unmarshalScalarTestData.json'),
            class: User::class,
        );

        $this->assertEquals('John Doe', $json->name);
        $this->assertEquals('John Doe', $json->person);
        $this->assertNull($json->title);
        $this->assertEquals('Title', $json->defaultTitle);
        $this->assertEquals(35, $json->age);
        $this->assertTrue($json->active);
        $this->assertEquals(1.93, $json->height);
    }

    public function testUnmarshalNoneNamedType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only properties with named types are supported');

        JSON::unmarshal(
            data: '{"name": "John Doe"}',
            class: NonNamedType::class,
        );
    }

    public function testInvalidStringMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map integer to string');

        JSON::unmarshal(
            data: '{"name": 1}',
            class: User::class,
        );
    }

    public function testInvalidIntMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to integer');

        JSON::unmarshal(
            data: '{"name": "John", "age": "one"}',
            class: User::class,
        );
    }

    public function testInvalidFloatMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to float');

        JSON::unmarshal(
            data: '{"name": "John", "age": 35, "height": "oneMeterFiveCentimeter"}',
            class: User::class,
        );
    }

    public function testInvalidBoolMapping(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not map string to bool');

        JSON::unmarshal(
            data: '{"name": "John", "age": 35, "height": 1.93, "active": "false"}',
            class: User::class,
        );
    }
}
