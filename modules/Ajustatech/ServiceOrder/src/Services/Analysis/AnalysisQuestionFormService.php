<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionDraftServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionMapperServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionPayloadServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionSanitizerServiceInterface;

class AnalysisQuestionFormService
{
    public function __construct(
        protected AnalysisQuestionSanitizerServiceInterface $sanitizer,
        protected AnalysisQuestionDraftServiceInterface $draft,
        protected AnalysisQuestionMapperServiceInterface $mapper,
        protected AnalysisQuestionPayloadServiceInterface $payload,
    ) {}

    public function sanitizeAnalysisForm(mixed $name, mixed $description, mixed $value, array $questions): array
    {
        return $this->sanitizer->sanitizeAnalysisForm($name, $description, $value, $questions);
    }

    public function sanitizeText(?string $value, int $limit): string
    {
        return $this->sanitizer->sanitizeText($value, $limit);
    }

    public function nullableValue(mixed $value): ?string
    {
        return $this->sanitizer->nullableValue($value);
    }

    public function newQuestionRow(int $sequence, ?string $type = null, array $flags = []): array
    {
        return $this->draft->newQuestionRow($sequence, $type, $flags);
    }

    public function fromPersistedQuestion(object $question): array
    {
        return $this->mapper->fromPersistedQuestion($question);
    }

    public function buildQuestionPayload(array $questions): array
    {
        return $this->payload->buildQuestionPayload($questions);
    }
}
