<?php

namespace FilippoToso\Microvel\Support;

use FilippoToso\Microvel\Framework;

class View
{
    public static function render($view, $data = [], $mergeData = [])
    {
        return static::get($view, $data, $mergeData)->render();
    }

    public static function get($view, $data = [], $mergeData = [])
    {
        return Framework::instance()
            ->view()
            ->make($view, $data, $mergeData);
    }

    public static function __callStatic($name, $arguments)
    {
        return Framework::instance()
            ->view()
            ->$name(...$arguments);
    }
}
