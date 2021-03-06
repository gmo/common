<?php

namespace Gmo\Common;

abstract class Enum
{
    /** @var mixed Enum value */
    protected $value;
    /** @var array */
    private static $instances = [];
    /** @var array Store existing constants in a static cache per object. */
    private static $cache = [];

    /**
     * Creates a new value of some type
     *
     * @param mixed $value
     *
     * @throws \UnexpectedValueException if incompatible type is given.
     */
    private function __construct($value)
    {
        if (!$this->isValid($value)) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }
        $this->value = $value;
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public static function create($value)
    {
        $class = static::class;
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = [];
        }
        if (!array_key_exists($value, self::$instances[$class])) {
            self::$instances[$class][$value] = new static($value);
        }

        return self::$instances[$class][$value];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @return mixed
     */
    public function getKey()
    {
        return static::search($this->value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(static::toList());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @return static[] Constant name in key, Enum instance in value
     */
    public static function values()
    {
        $values = [];
        foreach (static::toList() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    /**
     * Returns all possible values as an array
     *
     * @return array Constant name in key, constant value in value
     */
    public static function toList()
    {
        $class = static::class;
        if (!array_key_exists($class, self::$cache)) {
            $reflection = new \ReflectionClass($class);
            self::$cache[$class] = $reflection->getConstants();
        }

        return self::$cache[$class];
    }

    /**
     * Check if is valid enum value
     *
     * @param $value
     *
     * @return bool
     */
    public static function isValid($value)
    {
        return in_array($value, static::toList(), true);
    }

    /**
     * Check if is valid enum key
     *
     * @param $key
     *
     * @return bool
     */
    public static function isValidKey($key)
    {
        $list = static::toList();

        return isset($list[$key]);
    }

    /**
     * Return key for value
     *
     * @param $value
     *
     * @return mixed
     */
    public static function search($value)
    {
        return array_search($value, static::toList(), true);
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
     *
     * @param string $name
     * @param array  $args
     *
     * @return static
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $args)
    {
        $key = "static::$name";
        if (!defined($key)) {
            throw new \BadMethodCallException(
                "No static method or enum constant '$name' in class " . static::class
            );
        }

        return static::create(constant($key));
    }

    /**
     * Magic method for is-ers.
     *
     *     $enum = MyEnum::FOO();
     *     $test = $enum->isFoo(); // returns true
     *
     * @param string $name
     * @param array  $args
     *
     * @return bool
     */
    public function __call($name, $args)
    {
        if (Str::startsWith($name, 'is', false)) {
            $key = 'static::' . strtoupper(substr($name, 2));
            if (defined($key)) {
                return $this->value === constant($key);
            }
        }

        throw new \BadMethodCallException("No method '$name' in class " . static::class);
    }
}
