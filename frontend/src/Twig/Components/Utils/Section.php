<?php

namespace App\Twig\Components\Utils;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Section
{
    public bool $withGrid = true;
    public bool $withPadding = true;
    public array $gridItems = [];
    public int $columns = 1;
    public string $columnsWidth = 'equal';
    public string $containerClasses = '';
}
