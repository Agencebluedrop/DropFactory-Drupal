<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Link
{
    public string $label;
    public string $url;
    public bool $active = false;
    public bool $underline = true;
    public string $linkClasses = '';
}
