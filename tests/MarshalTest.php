<?php

declare(strict_types=1);

namespace Nauni\JSON\Tests;

use Nauni\JSON\JSON;
use Nauni\JSON\Tests\DataStructure\Address;
use Nauni\JSON\Tests\DataStructure\User;
use PHPUnit\Framework\TestCase;

class MarshalTest extends TestCase
{
    public function testMarshalRoundTripUser(): void
    {
        $u = JSON::unmarshal(JSON::embed('tests/testdata/unmarshalScalarTestData.json'), User::class);
        $json = JSON::marshal($u);
        $again = JSON::unmarshal($json, User::class);
        $this->assertSame($u->name, $again->name);
        $this->assertSame($u->age, $again->age);
        $this->assertSame($u->height, $again->height);
        $this->assertSame($u->active, $again->active);
        $this->assertSame($u->defaultTitle, $again->defaultTitle);
    }

    public function testMarshalOmitsIgnored(): void
    {
        $a = new Address();
        $a->street = 'S';
        $a->city = 'C';
        $a->region = null;
        $json = JSON::marshal($a);
        $this->assertStringContainsString('street', $json);
        $this->assertStringContainsString('city', $json);
    }

    public function testMarshalNested(): void
    {
        $a = new Address();
        $a->street = '1';
        $a->city = 'X';
        $a->region = 'R';
        $json = JSON::marshal($a);
        $b = JSON::unmarshal($json, Address::class);
        $this->assertSame('1', $b->street);
        $this->assertSame('R', $b->region);
    }
}
