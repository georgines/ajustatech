<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionSanitizerServiceInterface;

class AnalysisQuestionSanitizerService implements AnalysisQuestionSanitizerServiceInterface
{
    public function sanitizeAnalysisForm(mixed $name, mixed $description, mixed $value, array $questions): array
    {
        return [
            'name' => $this->sanitizeText((string) $name, 255),
            'description' => $this->sanitizeText((string) $description, 1000),
            'value' => is_numeric($value) ? round((float) $value, 2) : 0.0,
            'questions' => $questions,
        ];
    }

    public function sanitizeText(?string $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    public function nullableValue(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    public function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'off', 'no', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }
}
