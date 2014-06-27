<?php
namespace {
	if (!interface_exists('JsonSerializable')) {
		interface JsonSerializable {
			function jsonSerialize();
		}
	}
}

namespace GMO\Common {
	interface ISerializable extends \JsonSerializable, \Serializable {
		function toArray();
		function toJson();
		static function fromArray($obj);
		static function fromJson($json);
	}
}
