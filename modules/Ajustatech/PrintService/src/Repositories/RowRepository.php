<?php

namespace Ajustatech\PrintService\Repositories;

use Ajustatech\PrintService\Contracts\RowRepositoryInterface;
use Ajustatech\PrintService\Contracts\CellRepositoryInterface;

class RowRepository implements RowRepositoryInterface
{
    protected array $cells = [];
    protected int $topSpacing;
    protected int $bottomSpacing;

    public function __construct(int $topSpacing = 0, int $bottomSpacing = 0)
    {
        $this->topSpacing = $topSpacing;
        $this->bottomSpacing = $bottomSpacing;
    }

    public function addCell(CellRepositoryInterface $cell): static
    {
        $this->cells[] = $cell;
        return $this;
    }

    public function topSpacing(int $spacing): static
    {
        $this->topSpacing = $spacing;
        return $this;
    }

    public function bottomSpacing(int $spacing): static
    {
        $this->bottomSpacing = $spacing;
        return $this;
    }

    public function getTopSpacing(): int
    {
        return $this->topSpacing;
    }

    public function getBottomSpacing(): int
    {
        return $this->bottomSpacing;
    }

    public function getCells(): array
    {
        return $this->cells;
    }
}
