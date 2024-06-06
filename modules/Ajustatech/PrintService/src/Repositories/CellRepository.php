<?php

namespace Ajustatech\PrintService\Repositories;

use Ajustatech\PrintService\Contracts\CellRepositoryInterface;

class CellRepository implements CellRepositoryInterface
{
    protected string $text;
    protected ?string $bgColor = null;
    protected ?int $fontSize = null;
    protected ?string $textColor = null;
    protected ?int $width = null;
    protected ?string $textAlign = null;
    protected array $borderStyles = [
        'top' => '',
        'right' => '',
        'bottom' => '',
        'left' => ''
    ];
    protected int $colspan = 1;
    protected ?string $fontFamily = null;

    // public function __construct()
    // {
    //     $this->text = $text;
    // }

    public function setBgColor(string $color): static
    {
        $this->bgColor = $color;
        return $this;
    }

    public function setFontSize(int $size): static
    {
        $this->fontSize = $size;
        return $this;
    }

    public function setTextColor(string $color): static
    {
        $this->textColor = $color;
        return $this;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function alignTextLeft(): static
    {
        $this->textAlign = 'left';
        return $this;
    }

    public function alignTextCenter(): static
    {
        $this->textAlign = 'center';
        return $this;
    }

    public function alignTextRight(): static
    {
        $this->textAlign = 'right';
        return $this;
    }

    public function alignTextJustify(): static
    {
        $this->textAlign = 'justify';
        return $this;
    }

    public function setBorderStyle(string $position, string $style): static
    {
        if (array_key_exists($position, $this->borderStyles)) {
            $this->borderStyles[$position] = $style;
        }
        return $this;
    }

    public function setColspan(int $colspan): static
    {
        $this->colspan = $colspan;
        return $this;
    }

    public function setFontFamily(string $font): static
    {
        $this->fontFamily = $font;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'bg_color' => $this->bgColor,
            'font_size' => $this->fontSize,
            'text_color' => $this->textColor,
            'width' => $this->width,
            'text_align' => $this->textAlign,
            'border_styles' => $this->borderStyles,
            'colspan' => $this->colspan,
            'font_family' => $this->fontFamily
        ];
    }
}
