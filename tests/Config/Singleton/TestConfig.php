<?php

namespace Gmo\Common\Tests\Config\Singleton;

use Gmo\Common\Config\Config;

class TestConfig extends Config
{
    protected $PROJECT_DIR = '../..';
    protected $CONFIG_FILE = 'fixtures/config/testConfig.yml';
}
