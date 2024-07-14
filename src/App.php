<?php

namespace FilippoToso\Microvel;

use Illuminate\Container\Container;

class App extends Container
{
    /**
     * @return bool
     *
     * @see \Illuminate\Contracts\Foundation\Application::isDownForMaintenance()
     */
    public function isDownForMaintenance(): bool
    {
        return false;
    }

    /**
     * @param string|string[] $environments
     * @return string|bool
     *
     * @see \Illuminate\Contracts\Foundation\Application::environment()
     */
    public function environment(...$environments)
    {
        if (empty($environments)) {
            return 'standalone';
        }

        return in_array(
            'standalone',
            is_array($environments[0]) ? $environments[0] : $environments
        );
    }

    /**
     * @return string
     *
     * @see \Illuminate\Contracts\Foundation\Application::getNamespace()
     */
    public function getNamespace(): string
    {
        return 'App\\';
    }
}
