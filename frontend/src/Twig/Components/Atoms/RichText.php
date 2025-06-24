<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RichText
{
    public ?string $headline = null;
    public string $headlineTag = 'h2';
    public string $headingClasses = '';
    public ?string $text = null;
    public string $textClasses = '';
}
