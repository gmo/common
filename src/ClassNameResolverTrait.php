<?php

namespace Gmo\Common;

/**
 * @deprecated
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
