<?php

namespace Gmo\Common\Tests;

use Gmo\Common\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function test_contains()
    {
        $this->assertTrue(Str::contains("this", "t"));
        $this->assertTrue(Str::contains("this", "h"));
        $this->assertTrue(Str::contains("this", "i"));
        $this->assertTrue(Str::contains("this", "s"));
        $this->assertTrue(Str::contains("this", "th"));
        $this->assertTrue(Str::contains("this", "is"));
        $this->assertTrue(Str::contains("this", "this", false));

        $this->assertTrue(Str::contains("thIs", "is", false));
        $this->assertTrue(Str::contains("this", "THIS", false));
    }

    public function test_empty_returns_true()
    {
        $this->assertTrue(Str::contains("this", ""));
        $this->assertTrue(Str::contains("this", "", false));
        $this->assertTrue(Str::startsWith("this", ""));
        $this->assertTrue(Str::startsWith("this", "", false));
        $this->assertTrue(Str::endsWith("this", ""));
        $this->assertTrue(Str::endsWith("this", "", false));
    }

    public function test_does_not_contain()
    {
        $this->assertFalse(Str::contains("this", "q"));
        $this->assertFalse(Str::contains("this", "qthis"));
        $this->assertFalse(Str::contains("this", "thisq"));
        $this->assertFalse(Str::contains("this", "THIS"));
    }

    public function test_equals()
    {
        $this->assertTrue(Str::equals("this", "this"));
        $this->assertFalse(Str::equals("this", "THIS"));
        $this->assertTrue(Str::equals("this", "THIS", false));
    }

    public function test_startsWith()
    {
        $this->assertTrue(Str::startsWith("blah", "b"));
        $this->assertTrue(Str::startsWith("blah", "bla"));
        $this->assertTrue(Str::startsWith("blah", "blah"));

        $this->assertTrue(Str::startsWith("bLAh", "blah", false));
    }

    public function test_does_not_startsWith()
    {
        $this->assertFalse(Str::startsWith("blah", "q"));
        $this->assertFalse(Str::startsWith("blah", "blaq"));
        $this->assertFalse(Str::startsWith("blah", "qbla"));
        $this->assertFalse(Str::startsWith("blah", "lah"));

        $this->assertFalse(Str::startsWith("BLah", "lah"));
    }

    public function test_explode()
    {
        $this->assertSame(array("herp", "derp"), Str::explode("herp derp", " ")->toArray());
    }

    public function test_explode_empty_returns_empty_collection()
    {
        $this->assertTrue(Str::explode("herp derp", "")->isEmpty());
    }

    public function test_explode_not_containing_delimiter()
    {
        $this->assertSame('herp derp', Str::explode("herp derp", ",")->first());
    }

    public function testIsClassOneOf()
    {
        $this->assertTrue(Str::isClassOneOf(Str::class, Str::class, \Exception::class));
        $this->assertFalse(Str::isClassOneOf(Str::class, \Exception::class));
    }
}
