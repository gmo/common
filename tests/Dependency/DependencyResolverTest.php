<?php

namespace Gmo\Common\Tests\Dependency;

use Gmo\Common\Dependency\DependencyResolver;
use PHPUnit\Framework\TestCase;

/**
 * @requires PHP 5.5
 */
class DependencyResolverTest extends TestCase
{
    public function testMapSort()
    {
        $resolver = $this->createMapResolver();

        $sorted = $resolver->sort();

        $this->assertInstanceOf('Bolt\Collection\Bag', $sorted);
        $this->assertEquals(array('a', 'c', 'd', 'b'), $sorted->keys()->toArray());
    }

    public function testMapGet()
    {
        $resolver = $this->createMapResolver();

        $deps = $resolver->get('d');

        $this->assertInstanceOf('Bolt\Collection\Bag', $deps);
        $this->assertEquals(array('c', 'a'), $deps->toArray());
    }

    public function testMapAll()
    {
        $resolver = $this->createMapResolver();

        $all = $resolver->all();

        $this->assertInstanceOf('Bolt\Collection\Bag', $all);
        $expected = array(
            'a' => array(),
            'b' => array('a'),
            'c' => array('a'),
            'd' => array('c', 'a'),
        );
        $this->assertEquals($expected, $all->toArrayRecursive());
    }

    public function testListSort()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();
        list($d, $b, $a, $c) = $list;

        $sorted = $resolver->sort();

        $this->assertInstanceOf('Bolt\Collection\Bag', $sorted);
        $this->assertEquals(array($a, $c, $d, $b), $sorted->toArray());
    }

    public function testListGet()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();
        list($d, $b, $a, $c) = $list;

        $deps = $resolver->get($d);

        $this->assertInstanceOf('Bolt\Collection\Bag', $deps);
        $this->assertEquals(array('c', 'a'), $deps->toArray());
    }

    public function testListAll()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();

        $all = $resolver->all();

        $this->assertInstanceOf('Bolt\Collection\Bag', $all);
        $expected = array(
            'a' => array(),
            'b' => array('a'),
            'c' => array('a'),
            'd' => array('c', 'a'),
        );
        $this->assertEquals($expected, $all->toArrayRecursive());
    }

    /**
     * @expectedException \Gmo\Common\Exception\Dependency\CyclicDependencyException
     * @expectedExceptionMessage The items 'b', 'a' have a cyclic dependency.
     */
    public function testCyclicDependency()
    {
        $map = array(
            'a' => array('b'),
            'b' => array('a'),
        );
        $resolver = DependencyResolver::fromMap($map, function ($item) { return $item; });

        $resolver->get('a');
    }

    /**
     * @expectedException \GMO\Common\Exception\Dependency\UnknownDependencyException
     * @expectedExceptionMessage Dependency 'b' from item 'a' does not exist within data set.
     */
    public function testUnknownDependency()
    {
        $map = array(
            'a' => array('b'),
        );
        $resolver = DependencyResolver::fromMap($map, function ($item) { return $item; });

        $resolver->get('a');
    }

    private function createMapResolver()
    {
        $map = array(
            'd' => array('depends' => array('c')),
            'b' => array('depends' => array('a')),
            'a' => array('depends' => array()),
            'c' => array('depends' => array('a')),
        );
        $resolver = DependencyResolver::fromMap(
            $map,
            function ($item, $key) {
                return $item['depends'];
            }
        );

        return $resolver;
    }

    private function createListResolver()
    {
        $a = (object) array('name' => 'a', 'depends' => array());
        $b = (object) array('name' => 'b', 'depends' => array($a));
        $c = (object) array('name' => 'c', 'depends' => array($a));
        $d = (object) array('name' => 'd', 'depends' => array($c));
        $list = array($d, $b, $a, $c);
        $resolver = DependencyResolver::fromList(
            $list,
            function ($item) { return $item->name; },
            function ($item) { return $item->depends; }
        );

        return array($list, $resolver);
    }
}
