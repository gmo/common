<?php
namespace GMO\Common;

interface ISerializable {
	function toArray();
	function toJson();
}