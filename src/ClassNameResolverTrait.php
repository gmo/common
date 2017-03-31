<?php

namespace Gmo\Common;

Deprecated::cls('\Gmo\Common\ClassNameResolverTrait', 1.0);

/**
 * @deprecated will be removed in 2.0.
 */
trait ClassNameResolverTrait
{
    /**
     * The “Late Static Binding” class name
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
}
