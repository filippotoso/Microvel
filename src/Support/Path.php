<?php

namespace FilippoToso\Microvel\Support;

/**
 * @method static string storage($path = null)
 * @method static string resources($path = null)
 * @method static string assets($path = null)
 */
class Path
{
    public static function __callStatic($name, $arguments)
    {
        return realpath(rtrim(Config::get('paths.' . $name), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($arguments[0] ?? null, DIRECTORY_SEPARATOR));
    }
}
