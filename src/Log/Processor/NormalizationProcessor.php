<?php

namespace Gmo\Common\Log\Processor;

/**
 * Abstracts looping through the array data looking for an item to normalize
 */
abstract class NormalizationProcessor
{
    public function __invoke(array $record)
    {
        return $this->normalizeCollection($record);
    }

    protected abstract function normalize($data);

    protected function normalizeCollection($data)
    {
        if (!is_iterable($data)) {
            return $this->normalize($data);
        }
        $normalized = [];

        $count = 1;
        foreach ($data as $key => $value) {
            if ($count++ >= 1000) {
                $normalized['...'] = 'Over 1000 items, aborting normalization';
                break;
            }
            $normalized[$key] = $this->normalizeCollection($value);
        }

        return $normalized;
    }
}
