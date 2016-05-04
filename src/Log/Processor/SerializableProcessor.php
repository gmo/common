<?php
namespace Gmo\Common\Log\Processor;

use Gmo\Common\ISerializable;

/**
 * Normalizes {@see Gmo\Common\ISerializable} objects
 */
class SerializableProcessor extends NormalizationProcessor {

	protected function normalize($data) {
		if ($data instanceof ISerializable) {
			$data = $data->toArray();
		}
		return $data;
	}
}
