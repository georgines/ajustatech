<?php

namespace Ajustatech\PrintService\Contracts;

interface CellRepositoryInterface
{
    // public function __construct(string $text);
    public function setBgColor(string $color): static;
    public function setFontSize(int $size): static;
    public function setTextColor(string $color): static;
    public function setWidth(int $width): static;
    public function alignTextLeft(): static;
    public function alignTextCenter(): static;
    public function alignTextRight(): static;
    public function alignTextJustify(): static;
    public function setBorderStyle(string $position, string $style): static;
    public function setColspan(int $colspan): static;
    public function setFontFamily(string $font): static;
    public function toArray(): array;

}

