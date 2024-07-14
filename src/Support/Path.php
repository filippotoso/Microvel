<?php

namespace FilippoToso\Microvel\Support;

class Path
{
    public static function resource($path = null)
    {
        return realpath(rtrim(Config::get('paths.resources'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }

    public static function storage($path = null)
    {
        return realpath(rtrim(Config::get('paths.storage'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }
}
