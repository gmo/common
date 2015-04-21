<?php
namespace Gmo\Common\Log\Processor;

use Gmo\Common\Serialization\ISerializable;

/**
 * Normalizes {@see GMO\Common\Serialization\ISerializable} objects
 */
class SerializableProcessor extends NormalizationProcessor {

	protected function normalize($data) {
		if ($data instanceof ISerializable) {
			$data = $data->toArray();
		}
		return $data;
	}
}
