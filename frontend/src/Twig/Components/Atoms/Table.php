<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Table
{
    public array $tableCells = [];
    public ?array $tableHeader = [null];
    public bool $withTasks = true;
    public bool $withCheckbox = true;
}
