<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Hero
{
    public string $label;
    public string $buttonUrl = '';
    public string $buttonText = '';
}
