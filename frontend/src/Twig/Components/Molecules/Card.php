<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Card
{
    public bool $isOnline = true;
    public string $cardId;
    public string $label;
    public string $profile;
    public string $buttonExternaltext;
    public string $buttonExternalUrl;
    public string $image;
    public string $alt = '';
    public string $buttonUrl;
    public array $listTasks = [];

}
