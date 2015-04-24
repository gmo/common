<?php
namespace Gmo\Common\Log\Processor;

use Gmo\Common\Serialization\SerializableInterface;

/**
 * Normalizes {@see GMO\Common\Serialization\SerializableInterface} objects
 */
class SerializableProcessor extends NormalizationProcessor {

	protected function normalize($data) {
		if ($data instanceof SerializableInterface) {
			$data = $data->toArray();
		}
		return $data;
	}
}
