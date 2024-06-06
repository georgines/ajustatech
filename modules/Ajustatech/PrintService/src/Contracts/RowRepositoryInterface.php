<?php

namespace Ajustatech\PrintService\Contracts;

interface RowRepositoryInterface
{
    public function __construct(int $topSpacing = 0, int $bottomSpacing = 0);
    public function addCell(CellRepositoryInterface $cell): static;
    public function topSpacing(int $spacing): static;
    public function bottomSpacing(int $spacing): static;
    public function getTopSpacing(): int;
    public function getBottomSpacing(): int;
    public function getCells(): array;
}

