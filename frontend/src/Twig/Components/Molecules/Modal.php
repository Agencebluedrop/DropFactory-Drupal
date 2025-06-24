<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Modal
{
    public ?string $modalTitle = null;
    public string $modalText = '';
    public ?string $toggleButton = null;
    public bool $withButtons = false;
}
