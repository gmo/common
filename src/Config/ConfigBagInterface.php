<?php

namespace Gmo\Common\Config;

use Bolt\Collection\Bag;
use GMO\Common\Exception\ConfigException;

/**
 * A collection of values from config file(s).
 */
interface ConfigBagInterface
{
    /**
     * Returns a value using path-like syntax to retrieve nested data.
     *
     *     get('foo/bar/baz') // baz value
     *
     * Note that keys are required to exist unless the default parameter has been **explicitly** passed in.
     * For example, assuming key `foo` does not exist:
     *     get('foo') // will throw ConfigException
     *     get('foo', null) // will return null
     *
     * @param string $path    The path to traverse and retrieve a value from
     * @param mixed  $default The default value if the key does not exist
     *
     * @throws ConfigException If the key does not exist and a default value has not been given
     *
     * @return mixed
     *
     * @see Bag::getPath() for more info.
     */
    public function get($path, $default = null);

    /**
     * A shortcut for {@see get} that ensures the returned value is a Bag.
     *
     * @param string $path    The path to traverse and retrieve a value from
     * @param mixed  $default The default value if the key does not exist
     *
     * @throws ConfigException If the key does not exist and a default value has not been given
     *
     * @return Bag
     */
    public function getBag($path, $default = null);

    /**
     * A shortcut for {@see get} that ensures the returned value is a boolean.
     *
     * @param string    $path    The path to traverse and retrieve a value from
     * @param bool|null $default The default value if the key does not exist
     *
     * @throws ConfigException If the key does not exist and a default value has not been given
     *
     * @return bool
     */
    public function getBool($path, $default = null);

    /**
     * A shortcut for {@see get} that assumes the value is a path and resolves
     * it to an absolute path based on the project root.
     *
     * The default path is also resolved to an absolute path if it is used.
     *
     * @param string      $path    The path to traverse and retrieve a value from
     * @param string|null $default The default value if the key does not exist
     *
     * @throws ConfigException If the key does not exist and a default value has not been given
     *
     * @return string
     */
    public function getPath($path, $default = null);

    /**
     * Sets a value at the path given.
     * Keys will be created as needed to set the value.
     *
     * @param string $path
     * @param mixed  $value
     *
     * @see Bag::setPath() for more info.
     */
    public function set($path, $value);

    /**
     * Returns a child ConfigBagInterface for the given path. This allows sections of the
     * config to be delegated to different areas of the codebase.
     *
     * For example, these all do the same thing:
     *     $config->get('foo/bar/baz')
     *     $config->child('foo/bar')->get('baz')
     *     $config->child('foo')->child('bar')->get('baz')
     *
     * @param string      $path The path that the returned config bag should be based at.
     * @param string|null $cls  An optional ConfigBagInterface class name to use. Default
     *                          is a new instance of the current class.
     *
     * @return ConfigBagInterface|static
     */
    public function child($path, $cls = null);
}
