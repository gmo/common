<?php
namespace UnitTest\Collections;

use Gmo\Common\Collections\NestedCollection;

class NestedCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSinglePath() {
		$c = $this->getCollection();
		$this->assertSame(['bar' => 'hello'], $c->get('foo'));
	}

	public function testGetMultiplePath() {
		$c = $this->getCollection();
		$this->assertSame('hello', $c->get('foo/bar'));
	}

	public function testGetWithMissingItem() {
		$c = new NestedCollection();
		$this->assertSame('default', $c->get('foo/bar', 'default'));
	}

	public function testGetWithNonTraversableItem() {
		$c = new NestedCollection();
		$c->set('foo', 'hi');
		$this->assertSame('default', $c->get('foo/bar', 'default'));
	}

	public function testHasSinglePath() {
		$c = $this->getCollection();
		$this->assertTrue($c->has('foo'));
	}

	public function testHasMultiplePath() {
		$c = $this->getCollection();
		$this->assertTrue($c->has('foo/bar'));
	}

	public function testHasWithMissingItem() {
		$c = new NestedCollection();
		$this->assertFalse($c->has('foo/bar'));
	}

	public function testHasNonTraversableItem() {
		$c = new NestedCollection();
		$c->set('foo', 'hi');
		$this->assertFalse($c->has('foo/bar'));
	}

	public function testSetSinglePath() {
		$c = new NestedCollection();
		$c->set('foo', 'bar');
		$this->assertSame('bar', $c->get('foo'));
	}

	public function testSetMultiplePath() {
		$c = new NestedCollection();
		$c->set('foo/bar', 'hello');
		$this->assertSame('hello', $c->get('foo/bar'));
	}

	public function testSetMultiplePathWithArrayUnderCollection() {
		$c = new NestedCollection();
		$c->set('foo', []);
		$c->set('foo/bar', 'hello');
		$this->assertSame('hello', $c->get('foo/bar'));
	}

	public function testSetMultiplePathWithArrayUnderArray() {
		$c = new NestedCollection();
		$c->set('foo', []);
		$c->set('foo/bar', []);
		$c->set('foo/bar/baz', 'hello');
		$this->assertSame('hello', $c->get('foo/bar/baz'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Cannot modify an array that is under a ArrayAccess object (except for ArrayCollection).
	 */
	public function testSetMultiplePathFailsWithArrayUnderArrayAccess() {
		$c = new NestedCollection();
		$c->set('foo', new \ArrayObject());
		$c->set('foo/bar', []);
		$c->set('foo/bar/baz', 'hello');
	}

	public function testSetList() {
		$c = new NestedCollection();

		$c->set('foo/baz/[]', 'a');
		$c->set('foo/baz/[]', 'b');
		$list = $c->get('foo/baz');
		$this->assertInstanceOf(NestedCollection::className(), $list);
		$this->assertCount(2, $list);
		$this->assertSame('a', $list->first());
		$this->assertSame('b', $list->last());


		$c->set('hello/[]/world', 'foo');
	}

	public function testSetListAtRoot() {
		$c = new NestedCollection();
		$c->set('[]', 'foo');
		$this->assertSame('foo', $c->first());
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Trying to set path foo/[], but foo is set and is not array accessible
	 */
	public function testSetFailsWhenKeyNotArrayAccessible() {
		$c = new NestedCollection();
		$c->set('foo', 'bar');
		$c->set('foo/[]', 'blah');
	}

	public function testRemoveMultiplePathWithArrays() {
		$c = new NestedCollection([
			'foo' => [
				'bar' => 'hello',
				'baz' => [
					'hello' => 'world',
				],
			],
		]);
		$expected = new NestedCollection([
			'foo' => [
				'baz' => [
					'hello' => 'world',
				],
			],
		]);

		$c->remove('foo/bar');
		$this->assertEquals($expected, $c);

		$expected = new NestedCollection([
			'foo' => [],
		]);

		$c->remove('foo/baz');
		$this->assertEquals($expected, $c);
	}

	public function testRemoveMultiplePathWithCollections() {
		$c = NestedCollection::createRecursive([
			'foo' => [
				'bar' => 'hello',
				'baz' => [
					'hello' => 'world',
				],
			],
		]);
		$expected = [
			'foo' => [
				'bar' => 'hello',
				'baz' => [],
			],
		];

		$this->assertSame('world', $c->remove('foo/baz/hello'));
		$this->assertSame($expected, $c->toArrayRecursive());
	}

	public function testRemoveMultiplePathWithMissingKey() {
		$c = new NestedCollection();
		$this->assertNull($c->remove('foo/bar'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Trying to remove path foo/hello, but foo is not array accessible
	 */
	public function testRemoveMultiplePathWhenNotArrayAccessible() {
		$c = new NestedCollection();
		$butWhy = new \stdClass();
		$butWhy->hello = 'world';
		$c->set('foo', $butWhy);
		$c->remove('foo/hello');
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Trying to remove path foo/bar/baz/hello, but bar is not array accessible
	 */
	public function testRemoveMultiplePathWhenSubPathNotArrayAccessible() {
		$c = new NestedCollection();
		$butWhy = new \stdClass();
		$butWhy->hello = 'world';
		$c->set('foo/bar', $butWhy);
		$c->remove('foo/bar/baz/hello');
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Cannot modify an array that is under a ArrayAccess object (except for ArrayCollection).
	 */
	public function testRemoveMultiplePathFailsWithArrayUnderArrayAccess() {
		$c = new NestedCollection();
		$c->set('foo', new \ArrayObject());
		$c->set('foo/bar', ['hello' => 'world']);
		$c->remove('foo/bar/hello');
	}

	public function testRemoveWithMissingKey() {
		$c = new NestedCollection();
		$this->assertNull($c->remove('foo/bar'));
	}

	protected function getCollection() {
		return new NestedCollection([
			'foo' => [
				'bar' => 'hello',
			],
		]);
	}
}
