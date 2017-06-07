<?php

namespace GMO\Common\Log\Processor;

use GMO\Common\ISerializable;

/**
 * Normalizes {@see GMO\Common\ISerializable} objects
 */
class SerializableProcessor extends NormalizationProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function normalize($data)
    {
        if ($data instanceof ISerializable) {
            $data = $data->toArray();
        }

        return $data;
    }
}
