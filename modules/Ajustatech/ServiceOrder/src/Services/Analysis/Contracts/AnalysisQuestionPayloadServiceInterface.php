<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

interface AnalysisQuestionPayloadServiceInterface
{
    public function buildQuestionPayload(array $questions): array;

    public function normalizeOptions(array $options, string $questionType): array;

    public function normalizeAnswerProcedureMap(array $answerMap, string $questionType, array $options): array;
}
