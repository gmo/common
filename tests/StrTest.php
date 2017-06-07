<?php
namespace Gmo\Common\Tests;

use GMO\Common\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase {

	public function test_contains() {
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

	public function test_empty_returns_true() {
		$this->assertTrue(Str::contains("this", ""));
		$this->assertTrue(Str::contains("this", "", false));
		$this->assertTrue(Str::startsWith("this", ""));
		$this->assertTrue(Str::startsWith("this", "", false));
		$this->assertTrue(Str::endsWith("this", ""));
		$this->assertTrue(Str::endsWith("this", "", false));
	}

	public function test_does_not_contain() {
		$this->assertFalse(Str::contains("this", "q"));
		$this->assertFalse(Str::contains("this", "qthis"));
		$this->assertFalse(Str::contains("this", "thisq"));
		$this->assertFalse(Str::contains("this", "THIS"));
	}

	public function test_equals() {
		$this->assertTrue(Str::equals("this", "this"));
		$this->assertFalse(Str::equals("this", "THIS"));
		$this->assertTrue(Str::equals("this", "THIS", false));
	}

	public function test_startsWith() {
		$this->assertTrue(Str::startsWith("blah", "b"));
		$this->assertTrue(Str::startsWith("blah", "bla"));
		$this->assertTrue(Str::startsWith("blah", "blah"));

		$this->assertTrue(Str::startsWith("bLAh", "blah", false));
	}

	public function test_does_not_startsWith() {
		$this->assertFalse(Str::startsWith("blah", "q"));
		$this->assertFalse(Str::startsWith("blah", "blaq"));
		$this->assertFalse(Str::startsWith("blah", "qbla"));
		$this->assertFalse(Str::startsWith("blah", "lah"));

		$this->assertFalse(Str::startsWith("BLah", "lah"));
	}

	public function test_endsWith() {
		$this->assertTrue(Str::endsWith("blah", "h"));
		$this->assertTrue(Str::endsWith("blah", "ah"));
		$this->assertTrue(Str::endsWith("blah", "blah"));

		$this->assertTrue(Str::endsWith("blAH", "blah", false));
	}

	public function test_does_not_endsWith() {
		$this->assertFalse(Str::endsWith("blah", "q"));
		$this->assertFalse(Str::endsWith("blah", "qblah"));
		$this->assertFalse(Str::endsWith("blah", "lalh"));
		$this->assertFalse(Str::endsWith("blah", "lahq"));
		$this->assertFalse(Str::endsWith("blah", "BLAH"));
	}

    public function test_explode() {
        $this->assertSame(array("herp", "derp"), Str::explode("herp derp", " ")->toArray());
    }

    public function test_explode_empty_returns_empty_collection() {
        $this->assertTrue(Str::explode("herp derp", "")->isEmpty());
    }

    /**
     * @group legacy
     */
	public function test_split() {
		$this->assertSame(array("herp", "derp"), Str::split("herp derp", " ")->toArray());
	}

    /**
     * @group legacy
     */
	public function test_split_empty_returns_empty_collection() {
		$this->assertTrue(Str::split("herp derp", "")->isEmpty());
	}

	public function test_split_first_last_empty_returns_false() {
		$this->assertFalse(Str::splitFirst("herp derp", ""));
		$this->assertFalse(Str::splitLast("herp derp", ""));
	}

	public function testLegacy_split_not_containing_delimiter() {
		$this->assertSame('herp derp', Str::split("herp derp", ",")->first());
	}

	public function test_split_not_containing_delimiter() {
		$this->assertSame('herp derp', Str::explode("herp derp", ",")->first());
		$this->assertSame("herp derp", Str::splitFirst("herp derp", ","));
		$this->assertSame("herp derp", Str::splitLast("herp derp", ","));
	}

	public function test_split_first_containing_delimiter() {
		$this->assertSame("herp", Str::splitFirst("herp derp", " "));
	}

	public function test_split_last_containing_delimiter() {
		$this->assertSame("derp", Str::splitLast("herp derp", " "));
	}

	public function test_remove_not_containing_value() {
		$this->assertSame("asdf", Str::removeFirst("asdf", "zxc"));
		$this->assertSame("asdf", Str::removeLast("asdf", "zxc"));
	}

	public function test_remove_first_containing_value() {
		$this->assertSame("HelloHelloGoodbye", Str::removeFirst("HelloGoodbyeHelloGoodbye", "Goodbye"));
		$this->assertSame("HelloHelloGoodbye", Str::removeFirst("HelloGOODBYEHelloGoodbye", "goodbye", false));
	}

	public function test_remove_last_containing_value() {
		$this->assertSame("HelloGoodbyeGoodbye", Str::removeLast("HelloGoodbyeHelloGoodbye", "Hello"));
		$this->assertSame("HelloGoodbyeGoodbye", Str::removeLast("HelloGoodbyeHELLOGoodbye", "hello", false));
	}

	public function test_replace_first_containing_value() {
		$this->assertSame('HelloFooHelloGoodbye', Str::replaceFirst('HelloGoodbyeHelloGoodbye', 'Goodbye', 'Foo'));
		$this->assertSame('HelloFooHelloGoodbye', Str::replaceFirst('HelloGOODBYEHelloGoodbye', 'goodbye', 'Foo', false));
	}

	public function test_replace_last_containing_value() {
		$this->assertSame('HelloGoodbyeFooGoodbye', Str::replaceLast('HelloGoodbyeHelloGoodbye', 'Hello', 'Foo'));
		$this->assertSame('HelloGoodbyeFooGoodbye', Str::replaceLast('HelloGoodbyeHELLOGoodbye', 'hello', 'Foo', false));
	}

	public function test_class_name() {
		$this->assertSame('StrTest', Str::className($this));
		$this->assertSame('StrTest', Str::className(static::class));
	}

	public function test_class_name_does_not_exist() {
		$this->assertFalse(Str::className('ClassDoesNotExist'));
	}

}
