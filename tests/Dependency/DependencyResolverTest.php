<?php

namespace Gmo\Common\Tests\Dependency;

use Bolt\Collection\ImmutableBag;
use Gmo\Common\Dependency\DependencyResolver;
use PHPUnit\Framework\TestCase;

class DependencyResolverTest extends TestCase
{
    public function testMapSort()
    {
        $resolver = $this->createMapResolver();

        $sorted = $resolver->sort();

        $this->assertInstanceOf(ImmutableBag::class, $sorted);
        $this->assertEquals(['a', 'c', 'd', 'b'], $sorted->keys()->toArray());
    }

    public function testMapGet()
    {
        $resolver = $this->createMapResolver();

        $deps = $resolver->get('d');

        $this->assertInstanceOf(ImmutableBag::class, $deps);
        $this->assertEquals(['c', 'a'], $deps->toArray());
    }

    public function testMapAll()
    {
        $resolver = $this->createMapResolver();

        $all = $resolver->all();

        $this->assertInstanceOf(ImmutableBag::class, $all);
        $expected = [
            'a' => [],
            'b' => ['a'],
            'c' => ['a'],
            'd' => ['c', 'a'],
        ];
        $this->assertEquals($expected, $all->toArrayRecursive());
    }

    public function testListSort()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();
        list($d, $b, $a, $c) = $list;

        $sorted = $resolver->sort();

        $this->assertInstanceOf(ImmutableBag::class, $sorted);
        $this->assertEquals([$a, $c, $d, $b], $sorted->toArray());
    }

    public function testListGet()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();
        list($d, $b, $a, $c) = $list;

        $deps = $resolver->get($d);

        $this->assertInstanceOf(ImmutableBag::class, $deps);
        $this->assertEquals(['c', 'a'], $deps->toArray());
    }

    public function testListAll()
    {
        /** @var DependencyResolver $resolver */
        list($list, $resolver) = $this->createListResolver();

        $all = $resolver->all();

        $this->assertInstanceOf(ImmutableBag::class, $all);
        $expected = [
            'a' => [],
            'b' => ['a'],
            'c' => ['a'],
            'd' => ['c', 'a'],
        ];
        $this->assertEquals($expected, $all->toArrayRecursive());
    }

    /**
     * @expectedException \Gmo\Common\Exception\Dependency\CyclicDependencyException
     * @expectedExceptionMessage The items 'b', 'a' have a cyclic dependency.
     */
    public function testCyclicDependency()
    {
        $map = [
            'a' => ['b'],
            'b' => ['a'],
        ];
        $resolver = DependencyResolver::fromMap($map, function ($item) { return $item; });

        $resolver->get('a');
    }

    /**
     * @expectedException \GMO\Common\Exception\Dependency\UnknownDependencyException
     * @expectedExceptionMessage Dependency 'b' from item 'a' does not exist within data set.
     */
    public function testUnknownDependency()
    {
        $map = [
            'a' => ['b'],
        ];
        $resolver = DependencyResolver::fromMap($map, function ($item) { return $item; });

        $resolver->get('a');
    }

    private function createMapResolver()
    {
        $map = [
            'd' => ['depends' => ['c']],
            'b' => ['depends' => ['a']],
            'a' => ['depends' => []],
            'c' => ['depends' => ['a']],
        ];
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
        $a = (object) ['name' => 'a', 'depends' => []];
        $b = (object) ['name' => 'b', 'depends' => [$a]];
        $c = (object) ['name' => 'c', 'depends' => [$a]];
        $d = (object) ['name' => 'd', 'depends' => [$c]];
        $list = [$d, $b, $a, $c];
        $resolver = DependencyResolver::fromList(
            $list,
            function ($item) { return $item->name; },
            function ($item) { return $item->depends; }
        );

        return [$list, $resolver];
    }
}
