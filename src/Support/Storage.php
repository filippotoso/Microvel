<?php

namespace FilippoToso\Microvel\Support;

use FilippoToso\Microvel\Framework;

class Storage
{
    public static function __callStatic($name, $arguments)
    {
        return Framework::instance()->storage()->$name(...$arguments);
    }
}
