<?php

namespace Ajustatech\PrintService\Repositories;

use Ajustatech\PrintService\Contracts\DocumentsRepositoryInterface;
use Ajustatech\PrintService\Contracts\RowRepositoryInterface;

class DocumentsRepository implements DocumentsRepositoryInterface
{
    protected array $rows = [];

    public function addRow(RowRepositoryInterface $row): static
    {
        $this->rows[] = $row;
        return $this;
    }

    public function generateHtml(): string
    {
        $html = '<table class="table table-striped table-bordered">';

        foreach ($this->rows as $row) {
            $html .= '<tr style="margin-top: ' . $row->getTopSpacing() . 'px; margin-bottom: ' . $row->getBottomSpacing() . 'px;">';
            foreach ($row->getCells() as $cell) {
                $cellArray = $cell->toArray();
                $html .= '<td style="' . $this->getCellStyles($cellArray) . '" colspan="' . $cellArray['colspan'] . '">' . $cellArray['text'] . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    protected function getCellStyles(array $cell): string
    {
        $styles = '';

        if ($cell['bg_color']) {
            $styles .= 'background-color: ' . $cell['bg_color'] . ';';
        }
        if ($cell['font_size']) {
            $styles .= 'font-size: ' . $cell['font_size'] . 'px;';
        }
        if ($cell['text_color']) {
            $styles .= 'color: ' . $cell['text_color'] . ';';
        }
        if ($cell['width']) {
            $styles .= 'width: ' . $cell['width'] . '%;';
        }
        if ($cell['text_align']) {
            $styles .= 'text-align: ' . $cell['text_align'] . ';';
        }
        if ($cell['font_family']) {
            $styles .= 'font-family: ' . $cell['font_family'] . ';';
        }
        if ($cell['border_styles']) {
            foreach ($cell['border_styles'] as $position => $style) {
                if ($style) {
                    $styles .= 'border-' . $position . ': ' . $style . ';';
                }
            }
        }

        return $styles;
    }
}
