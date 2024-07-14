<?php

namespace FilippoToso\Microvel\Support;

use FilippoToso\Microvel\Framework;

class Config
{
    public static function get($key, $default = null)
    {
        return Framework::instance()->config($key, $default);
    }
}

function config($key, $default = null)
{
    return Config::get($key, $default);
}
