<?php
namespace GMO\Common;

interface ClassNameResolverInterface {

	/**
	 * Returns the fully qualified name of the called class
	 * @return string
	 */
	public static function className();
}
