<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

interface AnalysisQuestionDraftServiceInterface
{
    public function newQuestionRow(int $sequence, ?string $type = null, array $flags = []): array;
}
