<?php

namespace Gmo\Common\Tests;

use GMO\Common\DateTime;
use Gmo\Common\SerializableCarbon;
use PHPUnit\Framework\TestCase;

class SerializableCarbonTest extends TestCase
{
    /** @var SerializableCarbon */
    private $now;

    protected function setUp()
    {
        $this->now = SerializableCarbon::now();
    }

    public function testArray()
    {
        $this->assertEquals($this->now, SerializableCarbon::fromArray($this->now->toArray()));
    }

    public function testJson()
    {
        $this->assertEquals($this->now, SerializableCarbon::fromJson($this->now->toJson()));
    }

    public function testNativeSerialize()
    {
        $this->assertEquals($this->now, unserialize(serialize($this->now)));
    }

    public function testCarbonSerialize()
    {
        $this->assertEquals($this->now, SerializableCarbon::fromSerialized($this->now->serialize()));
    }

    public function testLegacyCompat()
    {
        $this->assertEquals($this->now, DateTime::fromArray($this->now->toArray()));
        $this->assertEquals($this->now, DateTime::fromJson($this->now->toJson()));

        $now = DateTime::now();
        $this->assertEquals($now, SerializableCarbon::fromArray($now->toArray()));
        $this->assertEquals($now, SerializableCarbon::fromJson($now->toJson()));
    }
}
