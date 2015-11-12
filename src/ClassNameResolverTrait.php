<?php

namespace Gmo\Common;

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
