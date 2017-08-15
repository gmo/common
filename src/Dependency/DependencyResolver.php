<?php

namespace Gmo\Common\Dependency;

use Bolt\Collection\Bag;
use Bolt\Collection\MutableBag;
use Gmo\Common\Exception\Dependency\CyclicDependencyException;
use Gmo\Common\Exception\Dependency\UnknownDependencyException;

/**
 * Provides logic for resolving item dependencies and sorting them.
 *
 * Example for maps:
 * <example>
 *     $map = [
 *         'd' => ['depends' => ['c']],
 *         'b' => ['depends' => ['a']],
 *         'a' => ['depends' => []],
 *         'c' => ['depends' => ['a']],
 *     ];
 *
 *     $resolver = DependencyResolver::fromMap($map, function ($item, $key) {
 *         return $item['depends'];
 *     });
 *
 *     $resolver->get('d'); // ['c', 'a']
 *     $sorted = $resolver->sort();
 *     $sorted->keys(); // ['a', 'c', 'd', 'b']
 * </example>
 *
 * Example for lists:
 * <example>
 *     $red = new Color(name = 'red');
 *     $blue = new Color(name = 'blue');
 *     $purple = new Color(name = 'purple', parents = [$red, $blue]);
 *     $pink = new Color(name = 'pink', parents = [$purple, $red]);
 *     $unsortedList = [$purple, $red, $pink, $blue];
 *
 *     $resolver = DependencyResolver::fromList(
 *         $unsortedList,
 *         function ($color) { // getId
 *             return $color->name;
 *         },
 *         function ($color) { // getDependencies
 *             return $color->parents;
 *         }
 *     );
 *
 *     $resolver->get($pink); // ['purple', 'red', 'blue']
 *     $resolver->sort(); // [$red, $blue, $purple, $pink]
 * </example>
 */
class DependencyResolver
{
    /** @var DependencyInterface */
    private $dependency;
    /** @var MutableBag|Bag[] */
    private $resolved;
    /** @var MutableBag */
    private $resolving;

    /**
     * Creates a DependencyResolver for a map with the callback given.
     *
     * @param iterable $map             The map of items
     * @param callable $getDependencies Function is called with (item, key)
     *
     * @return DependencyResolver
     */
    public static function fromMap($map, callable $getDependencies)
    {
        return new static(new CallbackMapDependency($map, $getDependencies));
    }

    /**
     * Creates a DependencyResolver for a list with the callbacks given.
     *
     * @param iterable $list            The list of items
     * @param callable $getId           Function is called with (item)
     * @param callable $getDependencies Function is called with (item)
     *
     * @return DependencyResolver
     */
    public static function fromList($list, callable $getId, callable $getDependencies)
    {
        return new static(new CallbackListDependency($list, $getId, $getDependencies));
    }

    /**
     * Constructor.
     *
     * @param DependencyInterface $dependency
     */
    public function __construct(DependencyInterface $dependency)
    {
        $this->dependency = $dependency;
        $this->resolved = new MutableBag();
        $this->resolving = new MutableBag();
    }

    /**
     * Determines all dependencies for the data set and sorts it so least dependant are first.
     *
     * @return Bag
     */
    public function sort()
    {
        $deps = $this->all();
        $sorted = $this->sortDeps($deps);

        return $this->dependency->getSorted($sorted);
    }

    /**
     * Returns all of the dependencies for the data set as a map.
     *
     * @return Bag|Bag[] [ ID => ID[] ]
     */
    public function all()
    {
        foreach ($this->dependency as $keyOrItem) {
            $this->get($keyOrItem);
        }

        return $this->resolved->immutable();
    }

    /**
     * Return all of the dependencies for the given key or item.
     *
     * @param mixed $keyOrItem
     *
     * @return mixed[]|Bag
     */
    public function get($keyOrItem)
    {
        $id = $this->dependency->getId($keyOrItem);

        if ($this->resolved->has($id)) {
            return $this->resolved[$id];
        }

        $deps = new MutableBag();

        $thisDeps = $this->dependency->getDependencies($keyOrItem);

        foreach ($thisDeps as $dep) {
            try {
                $this->dependency->verify($dep);
            } catch (UnknownDependencyException $e) {
                $e->setItem($id);
                throw $e;
            }
            $depId = $this->dependency->getId($dep);

            if (!$deps->hasItem($depId)) {
                $deps[] = $depId;
            }

            if ($this->resolving->has($depId)) {
                throw new CyclicDependencyException($this->resolving->keys()->toArray());
            }

            $this->resolving[$depId] = true;
            try {
                $deps = $deps->merge($this->get($dep));
            } finally {
                $this->resolving->remove($depId);
            }
        }

        $this->resolved[$id] = $deps->unique()->immutable();

        return $deps;
    }

    /**
     * Sorts the mapping of dependencies and returns an ordered list.
     *
     * @param Bag $toSort
     *
     * @return Bag
     */
    private function sortDeps(Bag $toSort)
    {
        $sorted = [];
        $toSort = $toSort->mutable();

        while (count($toSort) > 0) {
            foreach ($toSort as $name => $dependencies) {
                if (!array_diff($dependencies->toArray(), $sorted)) {
                    $sorted[] = $name;
                    unset($toSort[$name]);
                }
            }
        }

        return new Bag($sorted);
    }
}
