<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Image
{
    public string $image;
    public string $alt = '';
    public bool $withmask = true;
    public string $imageClasses = '';
}
