<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

interface AnalysisQuestionMapperServiceInterface
{
    public function fromPersistedQuestion(object $question): array;
}
