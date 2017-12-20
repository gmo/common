<?php

namespace Gmo\Common;

/**
 * This class provides shortcuts for trigger deprecation warnings for various things.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Deprecated
{
    /**
     * Shortcut for triggering a deprecation warning for a method.
     *
     * Example:
     *     class Foo
     *     {
     *         public function hello() {}
     *
     *         public function world()
     *         {
     *             Deprecated::method(3.3, 'hello');
     *         }
     *     }
     * Will trigger: "Foo::world() is deprecated since 3.3 and will be removed in 4.0. Use hello() instead."
     *
     * @param string|float|null $since   The version it was deprecated in.
     * @param string            $suggest A method or class or suggestion of what to use instead.
     * @param string|null       $method  The method name. Defaults to method called from.
     */
    public static function method($since = null, $suggest = '', $method = null)
    {
        // Shortcut for suggested method
        if ($suggest && preg_match('/\s/', $suggest) === 0) {
            // Append () if it is a method/function (not a class)
            if (!class_exists($suggest)) {
                $suggest .= '()';
            }
            $suggest = "Use $suggest instead.";
        }

        if ($method === null) {
            $traceLimit = PHP_VERSION_ID >= 50400;
            $trace = $traceLimit ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2) : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $caller = $trace[1];
            $function = $caller['function'];
            if ($function === '__construct') {
                static::cls($caller['class'], $since, $suggest);

                return;
            }
            if (in_array($function, array('__call', '__callStatic', '__set', '__get', '__isset', '__unset'), true)) {
                $trace = $traceLimit ? debug_backtrace(false, 2) : debug_backtrace(false);  // with args
                $caller = $trace[1];
                $caller['function'] = $caller['args'][0];
            }
            $method = (isset($caller['class']) ? $caller['class'] . '::' : '') . $caller['function'];
            if ($function === '__isset' || $function === '__unset') {
                static::warn(substr($function, 2) . "($method)", $since, $suggest);

                return;
            }
            if ($function === '__set' || $function === '__get') {
                static::warn(strtoupper($function{2}) . "etting $method", $since, $suggest);

                return;
            }
        }

        static::warn($method . '()', $since, $suggest);
    }

    /**
     * Shortcut for triggering a deprecation warning for a class.
     *
     * @param string            $class   The class that is deprecated.
     * @param string|float|null $since   The version it was deprecated in.
     * @param string            $suggest A class or suggestion of what to use instead.
     */
    public static function cls($class, $since = null, $suggest = null)
    {
        if ($suggest && preg_match('/\s/', $suggest) === 0) {
            $suggest = ltrim($suggest, '\\');
            $suggest = sprintf("Use $suggest instead.", $suggest);
        }
        $class = ltrim($class, '\\');

        static::warn($class, $since, $suggest);
    }

    /**
     * Shortcut for triggering a deprecation warning for a DI service.
     *
     * Example:
     *     Deprecated::service('foo', 3.3, 'bar'); // triggers warning: "Accessing $app['foo'] is deprecated since 3.3 and will be removed in 4.0. Use $app['bar'] instead."
     *
     * @param string            $name    The service that is deprecated.
     * @param string|float|null $since   The version it was deprecated in.
     * @param string            $suggest A service name or suggestion of what to use instead.
     *
     * @deprecated since 1.32 will be removed in 2.0.
     */
    public static function service($name, $since = null, $suggest = '')
    {
        static::method(1.32);

        if ($suggest && preg_match('/\s/', $suggest) === 0) {
            $suggest = sprintf("Use \$app['%s'] instead.", $suggest);
        }

        static::warn(sprintf("Accessing \$app['%s']", $name), $since, $suggest);
    }

    /**
     * Shortcut for triggering a deprecation warning for a subject.
     *
     * Example:
     *     Deprecated::warn('Doing foo'); // triggers warning: "Doing foo is deprecated."
     *     Deprecated::warn('Doing foo', 3.3); // triggers warning: "Doing foo is deprecated since 3.3 and will be removed in 4.0."
     *     Deprecated::warn('Doing foo', 3.3, 'Do bar instead'); // triggers warning: "Doing foo is deprecated since 3.3 and will be removed in 4.0. Do bar instead."
     *
     * @param string            $subject The thing that is deprecated.
     * @param string|float|null $since   The version it was deprecated in.
     * @param string            $suggest A suggestion of what to do instead.
     */
    public static function warn($subject, $since = null, $suggest = '')
    {
        $message = $subject . ' is deprecated';

        if ($since !== null) {
            $major = substr((string) $since, 0, 1) + 1;
            $message .= sprintf(' since %.1f and will be removed in %s.0.', $since, $major);
        } else {
            $message .= '.';
        }

        if ($suggest) {
            $message .= ' ' . $suggest;
        }

        static::raw($message);
    }

    /**
     * Trigger a deprecation warning.
     *
     * @param string $message The raw message.
     */
    public static function raw($message)
    {
        @trigger_error($message, E_USER_DEPRECATED);
    }
}
