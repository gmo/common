<?php
namespace UnitTest;

use Gmo\Common\Collections\ArrayCollection;

class ArrayCollectionTest extends \PHPUnit_Framework_TestCase {

	public function testCreation() {
		$this->assertCollection(array('a', 'b'), new ArrayCollection(array('a', 'b')));
		$this->assertCollection(array('a', 'b'), new ArrayCollection('a', 'b'));
		$this->assertCollection(array('a'), new ArrayCollection('a'));
		$this->assertCollection(array(), new ArrayCollection(null));
		$this->assertCollection(array(), new ArrayCollection());

		$this->assertCollection(array('a', 'b'), ArrayCollection::create(array('a', 'b')));
		$this->assertCollection(array('a', 'b'), ArrayCollection::create('a', 'b'));
		$this->assertCollection(array('a'), ArrayCollection::create('a'));
		$this->assertCollection(array(), ArrayCollection::create(null));
		$this->assertCollection(array(), ArrayCollection::create());
	}

	private function assertCollection($expected, ArrayCollection $actual) {
		$this->assertSame($expected, $actual->toArray());
	}
}
