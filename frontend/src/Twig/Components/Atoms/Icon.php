<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Icon
{
    public string $icon;
    public string $size = 'default';
    public string $iconColor = '';
}
