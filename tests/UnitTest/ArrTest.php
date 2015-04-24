<?php
namespace Gmo\Common\UnitTest;

use Gmo\Common\Collections\Arr;

class ArrTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->sut = array( "color1" => "red", "color2" => "blue" );
		$this->list = array( "item1", "item2", "item3" );
		$this->counts = array( "wins" => 2, "losses" => 0 );
	}

	public function test_get_has_key() {
		$this->assertSame("red", Arr::get($this->sut, "color1"));
		$this->assertSame("red", Arr::get(new \ArrayObject($this->sut), "color1"));
		$this->assertSame("item1", Arr::get($this->list, 0));
	}

	public function test_get_default_value() {
		$this->assertNull(Arr::get($this->sut, "color3"));
		$this->assertFalse(Arr::get($this->sut, "color3", false));
		$this->assertSame("black", Arr::get($this->sut, "color3", "black"));
	}

	public function test_contains_keys() {
		$this->assertTrue(Arr::containsKey($this->sut, "color1"));
		$this->assertFalse(Arr::containsKey($this->sut, "color3"));
	}

	public function test_contains_value() {
		$sut = array(
			'egg' => true,
			'cheese' => false,
			'hair' => 765,
			'goblins' => null,
			'ogres' => 'no ogres allowed in this array'
		);

		$this->assertTrue(Arr::containsValue($sut, null));
		$this->assertTrue(Arr::containsValue($sut, false));
		$this->assertTrue(Arr::containsValue($sut, 765));
		$this->assertFalse(Arr::containsValue($sut, 763));
		$this->assertFalse(Arr::containsValue($sut, 'egg'));
		$this->assertFalse(Arr::containsValue($sut, 'hhh'));
		$this->assertFalse(Arr::containsValue($sut, array()));
	}

	public function test_increment() {
		$actual = Arr::increment($this->counts, "wins");
		$this->assertSame(array( "wins" => 3, "losses" => 0 ), $actual);
	}

	public function test_increment_new_key() {
		$actual = Arr::increment($this->counts, "ties");
		$this->assertSame(array( "wins" => 2, "losses" => 0, "ties" => 1 ), $actual);
	}

	public function test_is_associative() {
		$this->assertTrue(Arr::isAssociative($this->sut));
		$this->assertFalse(Arr::isAssociative($this->list));
	}

	public function test_prepend() {
		$actual = Arr::prepend($this->list, "item0");
		$this->assertSame("item0", $actual[0]);
	}

	public function test_append() {
		$actual = Arr::append($this->list, "item4");
		$this->assertSame("item4", $actual[3]);
	}

	public function test_remove() {
		$actual = Arr::remove($this->sut, "color1");
		$actual = Arr::remove($actual, "color3");
		$this->assertSame(array("color2" => "blue"), $actual);

		$actual = Arr::remove($this->list, 1);
		$this->assertSame(array("item1", "item3"), $actual);
	}

	public function test_get_first() {
		$actual = Arr::getFirst($this->list);
		$this->assertSame("item1", $actual);
	}

	public function test_get_last() {
		$actual = Arr::getLast($this->list);
		$this->assertSame("item3", $actual);
	}

	public function test_get_tail() {
		$actual = Arr::getTail($this->sut);
		$this->assertSame(array("color2" => "blue"), $actual);

		$actual = Arr::getTail($this->list);
		$this->assertSame(array("item2", "item3"), $actual);
	}

	public function test_get_all_but_last() {
		$actual = Arr::getAllButLast($this->sut);
		$this->assertSame(array("color1" => "red"), $actual);

		$actual = Arr::getAllButLast($this->list);
		$this->assertSame(array("item1", "item2"), $actual);
	}

	public function test_merge() {
		$actual = Arr::merge($this->sut, array("color3" => "green"), array("color4" => "black"));
		$this->assertSame(array(
			"color1" => "red",
			"color2" => "blue",
			"color3" => "green",
			"color4" => "black",
		), $actual);

		$actual = Arr::merge(array("red"), "blue");
		$this->assertSame(array("red", "blue"), $actual);
	}

	public function test_flatten() {
		$input = array('a', 'b', array('c', 'd'), 'e', array('f' => 'ooops'), 'g');
		$expected = array('a', 'b', 'c', 'd', 'e', 'f' => 'ooops', 'g');
		$actual = Arr::flatten($input);
		$this->assertSame($expected, $actual);
	}

	public function test_pop_has_key() {
		$actual = Arr::pop($this->sut, "color2");
		$this->assertSame("blue", $actual);
		$this->assertSame(array("color1" => "red"), $this->sut);
	}

	public function test_pop_default_value() {
		$actual = Arr::pop($this->sut, "color3", "black");
		$this->assertSame("black", $actual);
		$this->assertSame(array("color1" => "red", "color2" => "blue"), $this->sut);
	}

	public function test_pop_first() {
		$actual = Arr::popFirst($this->sut);
		$this->assertSame("red", $actual);
		$this->assertSame(array("color2" => "blue"), $this->sut);

		$actual = Arr::popFirst($this->list);
		$this->assertSame("item1", $actual);
		$this->assertSame(array("item2", "item3"), $this->list);
	}

	public function test_pop_last() {
		$actual = Arr::popLast($this->sut);
		$this->assertSame("blue", $actual);
		$this->assertSame(array("color1" => "red"), $this->sut);

		$actual = Arr::popLast($this->list);
		$this->assertSame("item3", $actual);
		$this->assertSame(array("item1", "item2"), $this->list);
	}

	private $sut;
	private $list;
	private $counts;
}
