<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Logo
{
    public string $logo;
    public string $url;
    public string $classes;
}
