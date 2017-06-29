<?php

namespace Gmo\Common\Tests\Cache;

use Gmo\Common\Cache\ArrayPredis;

class ArrayPredisTest extends PredisTest
{
    public function createClient()
    {
        return new ArrayPredis();
    }
}
