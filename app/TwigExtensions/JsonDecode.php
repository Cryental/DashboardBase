<?php

namespace App\TwigExtensions;

use Illuminate\Support\Str as IlluminateStr;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use TwigBridge\Facade\Twig;

class JsonDecode extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'TwigBridge_Extension_JSON_Decode';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('json_decode', [$this, 'json_decode']
            ),
        ];
    }

    public function json_decode($str)
    {
        return json_decode($str);
    }
}
