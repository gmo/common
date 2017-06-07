<?php

namespace Gmo\Common\Log\Processor;

use Gmo\Common\Serialization\SerializableInterface;

/**
 * Normalizes {@see Gmo\Common\Serialization\SerializableInterface} objects
 */
class SerializableProcessor extends NormalizationProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function normalize($data)
    {
        if ($data instanceof SerializableInterface) {
            $data = $data->toArray();
        }

        return $data;
    }
}
