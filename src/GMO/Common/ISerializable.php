<?php
namespace GMO\Common;

interface ISerializable {
	function toArray();
	function toJson();
	static function fromArray($obj);
	static function fromJson($json);
}