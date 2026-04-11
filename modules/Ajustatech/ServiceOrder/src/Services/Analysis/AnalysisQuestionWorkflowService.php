<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;

class AnalysisQuestionWorkflowService
{
    public function resequenceQuestions(array $questions): array
    {
        return ServiceOrderAnalysisQuestion::resequenceQuestions($questions);
    }

    public function syncQuestionDependencies(array $questions): array
    {
        return ServiceOrderAnalysisQuestion::syncQuestionDependencies($questions);
    }

    public function removeQuestion(array $questions, int $index): array
    {
        return ServiceOrderAnalysisQuestion::removeQuestion($questions, $index);
    }

    public function moveQuestionUp(array $questions, int $index): array
    {
        return ServiceOrderAnalysisQuestion::moveQuestionUp($questions, $index);
    }

    public function moveQuestionDown(array $questions, int $index): array
    {
        return ServiceOrderAnalysisQuestion::moveQuestionDown($questions, $index);
    }

    public function canMoveQuestionUp(array $questions, int $index): bool
    {
        return ServiceOrderAnalysisQuestion::canMoveQuestionUp($questions, $index);
    }

    public function canMoveQuestionDown(array $questions, int $index): bool
    {
        return ServiceOrderAnalysisQuestion::canMoveQuestionDown($questions, $index);
    }

    public function getLastMainQuestionIndex(array $questions): ?int
    {
        return ServiceOrderAnalysisQuestion::getLastMainQuestionIndex($questions);
    }

    public function getMainQuestionNumber(array $questions, int $index): int
    {
        return ServiceOrderAnalysisQuestion::getMainQuestionNumber($questions, $index);
    }

    public function getSubquestionParentMainNumber(array $questions, int $index): int
    {
        return ServiceOrderAnalysisQuestion::getSubquestionParentMainNumber($questions, $index);
    }

    public function getSubquestionNumberInParent(array $questions, int $index): int
    {
        return ServiceOrderAnalysisQuestion::getSubquestionNumberInParent($questions, $index);
    }

    public function getSubquestionTriggerLabel(array $questions, int $index): string
    {
        return ServiceOrderAnalysisQuestion::getSubquestionTriggerLabel($questions, $index);
    }

    public function getNewSubquestionTriggerOptions(array $questions): array
    {
        return ServiceOrderAnalysisQuestion::getNewSubquestionTriggerOptions($questions);
    }

    public function getSubquestionTriggerOptionsForEdit(array $questions, int $index): array
    {
        return ServiceOrderAnalysisQuestion::getSubquestionTriggerOptionsForEdit($questions, $index);
    }

    public function getParentMainIndexFor(array $questions, int $index): ?int
    {
        return ServiceOrderAnalysisQuestion::getParentMainIndexFor($questions, $index);
    }

    public function getUsedTriggerValuesForParent(array $questions, int $parentIndex, ?int $ignoreQuestionIndex = null): array
    {
        return ServiceOrderAnalysisQuestion::getUsedTriggerValuesForParent($questions, $parentIndex, $ignoreQuestionIndex);
    }
}
