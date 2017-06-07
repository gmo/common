<?php

namespace GMO\Common;

Deprecated::cls('\GMO\Common\ClassNameResolverInterface', 1.0);

/**
 * @deprecated will be removed in 2.0.
 */
interface ClassNameResolverInterface
{
    /**
     * Returns the fully qualified name of the called class
     *
     * @return string
     */
    public static function className();
}
