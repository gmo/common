<?php

namespace GMO\Common\Log\Processor;

use Gmo\Common\Deprecated;

Deprecated::cls('\GMO\Common\Log\Processor\NormalizationProcessor', 1.32);

/**
 * Abstracts looping through the array data looking for an item to normalize
 *
 * @deprecated since 1.32 and will be removed in 2.0.
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
        $normalized = array();

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
