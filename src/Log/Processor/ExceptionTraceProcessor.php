<?php

namespace Gmo\Common\Log\Processor;

use Gmo\Common\ExceptionNormalizer;

/**
 * Normalizes exception traces for Monolog records
 */
class ExceptionTraceProcessor
{
    /** @var ExceptionNormalizer */
    private $normalizer;
    /** @var bool */
    private $shortTraces;

    public function __construct(string $rootPath, bool $shortTraces = true)
    {
        $this->normalizer = new ExceptionNormalizer($rootPath); // Created here to keep it internal to this library.
        $this->shortTraces = $shortTraces;
    }

    public function __invoke(array $record): array
    {
        if (!isset($record['context']['exception'])) {
            return $record;
        }

        /** @var \Throwable $e */
        $e = $record['context']['exception'];

        $traces = [];
        do {
            $traces[] = $this->normalizer->normalizeTrace($e);
        } while ($e = $e->getPrevious());
        $record['extra']['traces'] = $traces;

        if ($this->shortTraces) {
            $shortened = [];
            foreach ($traces as $i => $trace) {
                $shortened[] = isset($shortened[$i - 1])
                    ? $this->normalizer->shortenTrace($shortened[$i - 1], $trace)
                    : $trace;
            }

            $record['extra']['short_traces'] = $shortened;
        }

        return $record;
    }
}
