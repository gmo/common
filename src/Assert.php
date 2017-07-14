<?php

namespace Gmo\Common;

/**
 * @method static void nullOrIsArrayAccessible($value, $message = '')
 * @method static void nullOrInstanceOfAny($value, array $classes, $message = '')
 * @method static void nullOrIsIterable($value, $message = '')
 * @method static void allIsArrayAccessible(array $values, $message = '')
 * @method static void allIsInstanceOfAny(array $values, array $classes, $message = '')
 * @method static void allIsIterable(array $values, $message = '')
 */
class Assert extends \Webmozart\Assert\Assert
{
    public static function isArrayAccessible($value, $message = '')
    {
        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an array accessible. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function isInstanceOfAny($value, array $classes, $message = '')
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return;
            }
        }

        static::reportInvalidArgument(sprintf(
            $message ?: 'Expected an instance of any of %2$s. Got: %s',
            static::typeToString($value),
            implode(', ', array_map(array('static', 'valueToString'), $classes))
        ));
    }

    public static function isIterable($value, $message = '')
    {
        if (!is_iterable($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an iterable. Got: %s',
                static::typeToString($value)
            ));
        }
    }
}
