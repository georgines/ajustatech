<?php

namespace Ajustatech\PrintService\Contracts;

interface DocumentsRepositoryInterface
{
    public function addRow(RowRepositoryInterface $row): static;
    public function generateHtml(): string;
}

