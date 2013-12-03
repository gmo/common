<?php
namespace GMO\Common;

abstract class AbstractSerializable implements ISerializable {

	public function toArray() {
		$values = array();

		$objVars = get_object_vars( $this );
		foreach ( $objVars as $key => $value ) {
			if ( $value instanceof AbstractSerializable ) {
				$values[$key] = $value->toArray();
			} else {
				$values[$key] = $value;
			}
		}

		return $values;
	}
	public function toJson() {
		return json_encode( $this->toArray() );
	}

}