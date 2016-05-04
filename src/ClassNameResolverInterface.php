<?php
namespace GMO\Common;

/**
 * @deprecated
 */
interface ClassNameResolverInterface {

	/**
	 * Returns the fully qualified name of the called class
	 * @return string
	 */
	public static function className();
}
