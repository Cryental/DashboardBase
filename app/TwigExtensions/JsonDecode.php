<?php

namespace App\TwigExtensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

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
