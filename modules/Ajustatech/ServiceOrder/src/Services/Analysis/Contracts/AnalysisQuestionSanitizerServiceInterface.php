<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

interface AnalysisQuestionSanitizerServiceInterface
{
    public function sanitizeAnalysisForm(mixed $name, mixed $description, mixed $value, array $questions): array;

    public function sanitizeText(?string $value, int $limit): string;

    public function nullableValue(mixed $value): ?string;

    public function toBool(mixed $value): bool;
}
