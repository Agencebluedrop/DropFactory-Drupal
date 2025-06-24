<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Menu
{
    public array $menuItems = [];
    public bool $withUnderline = false;
    public string $variant = '';
}
