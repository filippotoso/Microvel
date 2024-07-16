<?php

namespace FilippoToso\Microvel\Support;

use FilippoToso\Microvel\Framework;

class View
{
    public static function render($view, $data = [], $mergeData = [])
    {
        return Framework::instance()
            ->view()
            ->make($view, $data, $mergeData)
            ->render();
    }
}

function view($view, $data = [], $mergeData = [])
{
    return View::render($view, $data, $mergeData);
}
