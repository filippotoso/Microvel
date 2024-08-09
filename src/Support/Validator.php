<?php

namespace FilippoToso\Microvel\Support;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Translation;

class Validator
{
    protected $factory;

    public function __construct($path = '', $locale = null)
    {
        $filesystem = new Filesystem\Filesystem();
        $fileLoader = new Translation\FileLoader($filesystem, $path);
        $translator = new Translation\Translator($fileLoader, $locale ?? Config::get('app.locale', 'en_US'));
        $this->factory = new Validation\Factory($translator);
    }

    public static function make($path = '', $locale = null)
    {
        return new static($path, $locale);
    }

    public function process($rules, $data = null, $messages = [])
    {
        $messages = empty($messages) ? Arr::dot(include(Path::resources('messages/validation.php'))) : $messages;

        if (is_null($data)) {
            $request = Request::capture();
            $data = $request->isJson() ? $request->json() : $request->all();
        }

        return $this->factory->make($data, $rules, $messages);
    }
}
