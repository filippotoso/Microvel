<?php

namespace FilippoToso\Microvel\Support;

use FilippoToso\Microvel\Framework;

class Route
{
    public static function __callStatic($name, $arguments)
    {
        return Framework::instance()->router()->$name(...$arguments);
    }
}
