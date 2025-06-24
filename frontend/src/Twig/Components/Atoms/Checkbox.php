<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Checkbox
{
    public string $checkboxId;
    public ?string $changeEvent = null;

}
