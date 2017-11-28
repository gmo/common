<?php

namespace Gmo\Common;

use Twig\Template;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
final class ExceptionNormalizer
{
    /** @var null|string */
    protected $rootDir;

    public function __construct(?string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function shortenTrace(array $main, array $inner): array
    {
        $main = array_reverse($main);
        $inner = array_reverse($inner);

        $removed = 0;
        for ($i = 0, $count = count($inner); $i < $count; ++$i) {
            if (!isset($main[$i])) {
                break;
            }
            if ($main[$i] == $inner[$i]) {
                unset($inner[$i]);
                ++$removed;
            }
        }

        $inner = array_reverse($inner);
        if ($removed > 0) {
            $inner[] = ['removed' => $removed];
        }

        return $inner;
    }

    public function normalizeTrace(\Throwable $e): array
    {
        $trace = $e->getTrace();
        array_unshift($trace, [
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        // Merge call_user_func and call_user_func_array frames into the previous frame
        // so it doesn't look like a duplicated frame.
        for ($i = count($trace) - 1; $i > 0; --$i) {
            if (($trace[$i]['function'] ?? null) === 'call_user_func' || ($trace[$i]['function'] ?? null) === 'call_user_func_array') {
                $trace[$i - 1]['file'] = $trace[$i]['file'];
                $trace[$i - 1]['line'] = $trace[$i]['line'];
                unset($trace[$i]);
                continue;
            }
        }
        $trace = array_values($trace);

        // Twig: Add template name to frame and replace class name.
        foreach ($trace as $i => &$frame) {
            if (isset($frame['class']) && is_subclass_of($frame['class'], Template::class, true)) {
                $frame['template'] = (new \ReflectionClass($frame['class']))
                    ->newInstanceWithoutConstructor()
                    ->getTemplateName()
                ;
                $frame['class_orig'] = $frame['class'];
                $frame['class'] = 'Template(' . $frame['template'] . ')';
            }

            if ($this->rootDir && isset($frame['file'])) {
                $frame['file'] = strpos($frame['file'], "eval()'d code") !== false
                    ? "eval()'d code"
                    : Path::makeRelative($frame['file'], $this->rootDir);
            }
        }

        return $trace;
    }
}
