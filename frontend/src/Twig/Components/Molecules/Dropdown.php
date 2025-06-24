<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Dropdown
{
    public string $label = 'Tâches';
    public array $listItems = [
        ['icon' => 'verify', 'label' => 'Vérifier le site'],
        ['icon' => 'reinitialise', 'label' => 'Réinitialiser le mot de passe'],
        ['icon' => 'save', 'label' => 'Sauvegarder le site'],
        ['icon' => 'view-saves', 'label' => 'Voir toutes les sauvegardes'],
        ['icon' => 'empty-cache', 'label' => 'Vider les caches'],
    ];
    
}
