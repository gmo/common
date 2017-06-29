<?php

namespace GMO\Common\Log\Processor;

use Gmo\Common\Deprecated;
use GMO\Common\ISerializable;

Deprecated::cls('\GMO\Common\Log\Processor\SerializableProcessor');

/**
 * Normalizes {@see GMO\Common\ISerializable} objects
 *
 * @deprecated will be removed in 2.0.
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
