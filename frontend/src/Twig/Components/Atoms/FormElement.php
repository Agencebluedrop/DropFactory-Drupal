<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FormElement
{
    public string $label;
    public string $inputId;
    public string $inputType = 'text';
    public string $name = '';
    public ?string $value = null;
    public ?string $autocompleteVal = null;
    public bool $required = false;
    public string $additionalAttributes = '';
}
