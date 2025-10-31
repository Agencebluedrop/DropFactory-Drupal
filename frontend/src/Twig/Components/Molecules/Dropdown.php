<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Dropdown
{
    public string $label = 'Tasks';
    public array $listItems = [
        ['icon' => 'verify', 'label' => 'Site verify'],
        ['icon' => 'reinitialise', 'label' => 'Reset password'],
        ['icon' => 'save', 'label' => 'Backup site'],
        ['icon' => 'view-saves', 'label' => 'View backups'],
        ['icon' => 'empty-cache', 'label' => 'Clear caches'],
    ];
    
}
