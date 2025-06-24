<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Button
{
    public string $label;
    public ?string $url = null;
    public ?string $alpineUrl = null;
    public string $variant = 'default';
    public ?string $icon = null;
    public string $size = 'default';
    public string $iconSize = 'default';
    public string $btnIconColor = '';
    public string $buttonClasses = '';
    public ?string $clickEvent = null;
    public ?string $type = null;
    public ?string $additionalAtt = null;
    public bool $buttonIcon = false;
    public bool $rounded = false;
    public bool $buttonContent = false;
}
