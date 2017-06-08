<?php

namespace Gmo\Common\Tests\Serialization;

use Gmo\Common\Serialization\SerializableCarbon;
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

    public function testNativeSerialize()
    {
        $this->assertEquals($this->now, unserialize(serialize($this->now)));
    }

    public function testCarbonSerialize()
    {
        $this->assertEquals($this->now, SerializableCarbon::fromSerialized($this->now->serialize()));
    }
}
