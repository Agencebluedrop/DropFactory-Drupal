<?php

namespace App\Twig\Components\Utils;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Grid
{
    public array $items = [];
    public int $columns = 1;
    public string $columnsWidth = 'equal';
}
