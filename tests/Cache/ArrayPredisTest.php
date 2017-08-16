<?php

namespace Gmo\Common\Tests\Cache;

use Gmo\Common\Cache\ArrayPredis;

/**
 * @group time-sensitive
 */
class ArrayPredisTest extends PredisTest
{
    public function createClient()
    {
        return new ArrayPredis();
    }
}
