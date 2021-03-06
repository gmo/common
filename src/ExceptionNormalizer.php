<?php

namespace Gmo\Common;

use Doctrine\Common\Persistence\Proxy as DoctrineProxy;
use ProxyManager\Proxy\ProxyInterface as OcramiusProxy;
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
            if (!isset($main[$i]) || $main[$i] != $inner[$i]) {
                break;
            }
            unset($inner[$i]);
            ++$removed;
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

        foreach ($trace as $i => &$frame) {
            if (isset($frame['class'])) {
                // Twig: Add template name to frame and replace class name.
                if (is_subclass_of($frame['class'], Template::class, true)) {
                    $frame['template'] = (new \ReflectionClass($frame['class']))
                        ->newInstanceWithoutConstructor()
                        ->getTemplateName()
                    ;
                    $frame['class_orig'] = $frame['class'];
                    $frame['class'] = 'Template(' . $frame['template'] . ')';

                    // Previous call is from generated file so remove it
                    unset($trace[$i - 1]['file'], $trace[$i - 1]['line']);
                }

                // Proxy: Add proxied name to frame and replace class name.
                if (Str::isClassOneOf($frame['class'], DoctrineProxy::class, OcramiusProxy::class)) {
                    $frame['proxied'] = get_parent_class($frame['class']);
                    $frame['class_orig'] = $frame['class'];
                    $frame['class'] = 'Proxy(' . $frame['proxied'] . ')';

                    // Previous call is from generated file so remove it
                    unset($trace[$i - 1]['file'], $trace[$i - 1]['line']);
                }
            }

            if (isset($frame['file'])) {
                if (strpos($frame['file'], "eval()'d code") !== false) {
                    $frame['file'] = "eval()'d code";
                } elseif ($this->rootDir) {
                    $frame['file'] = Path::makeRelative($frame['file'], $this->rootDir);
                }
            }
        }

        return $trace;
    }
}
