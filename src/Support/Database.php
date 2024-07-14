<?php

namespace FilippoToso\Microvel\Support;

use Closure;
use FilippoToso\Microvel\Framework;

class Database
{
    public function transaction(Closure $callback, $attempts = 1)
    {
        return Framework::instance()->database()
            ->transaction($callback, $attempts);
    }
}
