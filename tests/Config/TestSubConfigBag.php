<?php

namespace Gmo\Common\Tests\Config;

use Gmo\Common\Config\ConfigBag;

class TestSubConfigBag extends ConfigBag
{
    public function getHello()
    {
        return $this->get('hello');
    }
}
