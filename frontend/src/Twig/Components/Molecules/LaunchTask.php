<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class LaunchTask
{
    public array $optionsArray = [
        ['label' => 'clear caches', 'behavior' => '/clear-cache'],
    ];
    public string $selectUrl = '';
    
}
