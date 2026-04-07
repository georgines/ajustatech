<?php

namespace Ajustatech\ServiceOrder\Database\Models\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisQuestionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderAnalysisQuestion extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_YES_NO = 'yes_no';

    public const TYPE_SELECT = 'select';

    protected $table = 'service_order_analysis_questions';

    protected $fillable = [
        'analysis_service_id',
        'parent_question_id',
        'sequence',
        'section_name',
        'question_text',
        'question_type',
        'is_required',
        'technical_description',
        'is_technical_description_required',
        'images_json',
        'is_image_required',
        'required_images_count',
        'has_help',
        'help_content',
        'is_collapsed',
        'options_json',
        'condition_value',
        'answer_procedure_map_json',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_technical_description_required' => 'boolean',
        'is_image_required' => 'boolean',
        'required_images_count' => 'integer',
        'has_help' => 'boolean',
        'is_collapsed' => 'boolean',
        'images_json' => 'array',
        'options_json' => 'array',
        'answer_procedure_map_json' => 'array',
    ];

    protected static function newFactory()
    {
        return ServiceOrderAnalysisQuestionFactory::new();
    }

    public function analysisService(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisService::class, 'analysis_service_id');
    }

    public function parentQuestion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_question_id');
    }

    public static function allowedTypes(): array
    {
        return [
            self::TYPE_YES_NO,
            self::TYPE_SELECT,
        ];
    }

    public static function resequenceQuestions(array $questions): array
    {
        foreach ($questions as $index => $question) {
            $questions[$index]['sequence'] = $index + 1;
        }

        return array_values($questions);
    }

    public static function syncQuestionDependencies(array $questions): array
    {
        foreach ($questions as $index => $question) {
            $isSubquestion = (bool) ($question['is_subquestion'] ?? false);
            $parentIndex = self::getParentMainIndexFor($questions, $index);

            if ($isSubquestion && $parentIndex !== null) {
                $questions[$index]['parent_client_key'] = (string) ($questions[$parentIndex]['client_key'] ?? '');

                continue;
            }

            if ($isSubquestion && $index === 0) {
                $questions[$index]['parent_client_key'] = '';

                continue;
            }

            $questions[$index]['is_subquestion'] = false;
            $questions[$index]['parent_client_key'] = '';
            $questions[$index]['condition_value'] = '';
        }

        return array_values($questions);
    }

    public static function removeQuestion(array $questions, int $index): array
    {
        if (! isset($questions[$index])) {
            return $questions;
        }

        if ((bool) ($questions[$index]['is_subquestion'] ?? false)) {
            unset($questions[$index]);

            return array_values($questions);
        }

        $mainEnd = self::getMainBlockEndIndex($questions, $index);
        if ($mainEnd === null) {
            return $questions;
        }

        return array_values(array_merge(
            array_slice($questions, 0, $index),
            array_slice($questions, $mainEnd + 1)
        ));
    }

    public static function moveQuestionUp(array $questions, int $index): array
    {
        $mainStart = self::resolveMainStartIndex($questions, $index);
        if ($mainStart === null) {
            return $questions;
        }

        $previousMainStart = self::getPreviousMainQuestionIndex($questions, $mainStart);
        if ($previousMainStart === null) {
            return $questions;
        }

        $mainEnd = self::getMainBlockEndIndex($questions, $mainStart);
        $previousMainEnd = self::getMainBlockEndIndex($questions, $previousMainStart);
        if ($mainEnd === null || $previousMainEnd === null) {
            return $questions;
        }

        $before = array_slice($questions, 0, $previousMainStart);
        $previousBlock = array_slice($questions, $previousMainStart, $previousMainEnd - $previousMainStart + 1);
        $currentBlock = array_slice($questions, $mainStart, $mainEnd - $mainStart + 1);
        $after = array_slice($questions, $mainEnd + 1);

        return array_values(array_merge($before, $currentBlock, $previousBlock, $after));
    }

    public static function moveQuestionDown(array $questions, int $index): array
    {
        $mainStart = self::resolveMainStartIndex($questions, $index);
        if ($mainStart === null) {
            return $questions;
        }

        $mainEnd = self::getMainBlockEndIndex($questions, $mainStart);
        if ($mainEnd === null) {
            return $questions;
        }

        $nextMainStart = self::getNextMainQuestionIndex($questions, $mainEnd + 1);
        if ($nextMainStart === null) {
            return $questions;
        }

        $nextMainEnd = self::getMainBlockEndIndex($questions, $nextMainStart);
        if ($nextMainEnd === null) {
            return $questions;
        }

        $before = array_slice($questions, 0, $mainStart);
        $currentBlock = array_slice($questions, $mainStart, $mainEnd - $mainStart + 1);
        $nextBlock = array_slice($questions, $nextMainStart, $nextMainEnd - $nextMainStart + 1);
        $after = array_slice($questions, $nextMainEnd + 1);

        return array_values(array_merge($before, $nextBlock, $currentBlock, $after));
    }

    public static function canMoveQuestionUp(array $questions, int $index): bool
    {
        $mainStart = self::resolveMainStartIndex($questions, $index);

        return $mainStart !== null && self::getPreviousMainQuestionIndex($questions, $mainStart) !== null;
    }

    public static function canMoveQuestionDown(array $questions, int $index): bool
    {
        $mainStart = self::resolveMainStartIndex($questions, $index);
        if ($mainStart === null) {
            return false;
        }

        $mainEnd = self::getMainBlockEndIndex($questions, $mainStart);
        if ($mainEnd === null) {
            return false;
        }

        return self::getNextMainQuestionIndex($questions, $mainEnd + 1) !== null;
    }

    public static function getLastMainQuestionIndex(array $questions): ?int
    {
        for ($i = count($questions) - 1; $i >= 0; $i--) {
            if (! ($questions[$i]['is_subquestion'] ?? false)) {
                return $i;
            }
        }

        return null;
    }

    public static function getMainQuestionNumber(array $questions, int $index): int
    {
        $number = 0;
        for ($i = 0; $i <= $index; $i++) {
            if (($questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }

            $number++;
        }

        return max(1, $number);
    }

    public static function getSubquestionParentMainNumber(array $questions, int $index): int
    {
        $parentIndex = self::getParentMainIndexFor($questions, $index);
        if ($parentIndex === null) {
            return 0;
        }

        return self::getMainQuestionNumber($questions, $parentIndex);
    }

    public static function getSubquestionNumberInParent(array $questions, int $index): int
    {
        $parentIndex = self::getParentMainIndexFor($questions, $index);
        if ($parentIndex === null) {
            return 0;
        }

        $number = 0;
        for ($i = $parentIndex + 1; $i <= $index; $i++) {
            if (! ($questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }

            if (self::getParentMainIndexFor($questions, $i) !== $parentIndex) {
                continue;
            }

            $number++;
        }

        return max(1, $number);
    }

    public static function getSubquestionTriggerLabel(array $questions, int $index): string
    {
        $value = (string) ($questions[$index]['condition_value'] ?? '');
        if ($value === '') {
            return '';
        }

        $option = collect(self::getSubquestionTriggerOptionsForEdit($questions, $index))
            ->first(fn (array $item) => (string) ($item['value'] ?? '') === $value);

        return (string) ($option['label'] ?? '');
    }

    public static function getNewSubquestionTriggerOptions(array $questions): array
    {
        $parentIndex = self::getLastMainQuestionIndex($questions);
        if ($parentIndex === null) {
            return [];
        }

        $usedValues = self::getUsedTriggerValuesForParent($questions, $parentIndex);

        return collect(self::getTriggerOptionsForQuestion($questions, $parentIndex))
            ->filter(fn (array $option) => ! in_array((string) ($option['value'] ?? ''), $usedValues, true))
            ->values()
            ->all();
    }

    public static function getSubquestionTriggerOptionsForEdit(array $questions, int $index): array
    {
        $parentIndex = self::getParentMainIndexFor($questions, $index);
        if ($parentIndex === null) {
            return [];
        }

        $currentValue = trim((string) ($questions[$index]['condition_value'] ?? ''));
        $usedValues = self::getUsedTriggerValuesForParent($questions, $parentIndex, $index);

        return collect(self::getTriggerOptionsForQuestion($questions, $parentIndex))
            ->filter(function (array $option) use ($usedValues, $currentValue) {
                $value = (string) ($option['value'] ?? '');
                if ($value === $currentValue) {
                    return true;
                }

                return ! in_array($value, $usedValues, true);
            })
            ->values()
            ->all();
    }

    public static function syncForService(ServiceOrderAnalysisService $service, array $questions): void
    {
        static::syncManyForServices([
            [
                'analysis_service_id' => $service->id,
                'questions' => $questions,
            ],
        ]);
    }

    public static function syncManyForServices(array $serviceQuestionBatches): void
    {
        if (empty($serviceQuestionBatches)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($serviceQuestionBatches as $batch) {
            $analysisServiceId = (string) ($batch['analysis_service_id'] ?? '');
            if ($analysisServiceId === '') {
                continue;
            }

            $normalizedQuestions = [];
            $idByClientKey = [];
            foreach ((array) ($batch['questions'] ?? []) as $question) {
                $clientKey = (string) ($question['client_key'] ?? '');
                if ($clientKey === '') {
                    $clientKey = (string) Str::uuid();
                }

                $question['client_key'] = $clientKey;
                $normalizedQuestions[] = $question;
                $idByClientKey[$clientKey] = (string) Str::uuid();
            }

            foreach ($normalizedQuestions as $question) {
                $clientKey = (string) $question['client_key'];
                $parentClientKey = (string) ($question['parent_client_key'] ?? '');

                $rows[] = [
                    'id' => $idByClientKey[$clientKey],
                    'analysis_service_id' => $analysisServiceId,
                    'parent_question_id' => $parentClientKey !== '' && isset($idByClientKey[$parentClientKey])
                        ? $idByClientKey[$parentClientKey]
                        : null,
                    'sequence' => (int) ($question['sequence'] ?? 0),
                    'section_name' => (string) ($question['section_name'] ?? 'Geral'),
                    'question_text' => (string) ($question['question_text'] ?? ''),
                    'question_type' => (string) ($question['question_type'] ?? self::TYPE_YES_NO),
                    'is_required' => (bool) ($question['is_required'] ?? false),
                    'technical_description' => $question['technical_description'] ?? null,
                    'is_technical_description_required' => (bool) ($question['is_technical_description_required'] ?? false),
                    'images_json' => is_array($question['images_json'] ?? null) ? json_encode($question['images_json']) : ($question['images_json'] ?? null),
                    'is_image_required' => (bool) ($question['is_image_required'] ?? false),
                    'required_images_count' => $question['required_images_count'] ?? null,
                    'has_help' => (bool) ($question['has_help'] ?? false),
                    'help_content' => $question['help_content'] ?? null,
                    'is_collapsed' => (bool) ($question['is_collapsed'] ?? false),
                    'options_json' => is_array($question['options_json'] ?? null) ? json_encode($question['options_json']) : ($question['options_json'] ?? null),
                    'condition_value' => $question['condition_value'] ?? null,
                    'answer_procedure_map_json' => is_array($question['answer_procedure_map_json'] ?? null) ? json_encode($question['answer_procedure_map_json']) : ($question['answer_procedure_map_json'] ?? null),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }

    public function isVisible(array $answersByQuestionId): bool
    {
        if (! $this->parent_question_id) {
            return true;
        }

        $parentAnswer = $answersByQuestionId[$this->parent_question_id] ?? null;
        if ($parentAnswer === null) {
            return false;
        }

        $parentValue = strtolower(trim((string) $parentAnswer));
        $conditionValue = strtolower(trim((string) ($this->condition_value ?? '')));

        if ($conditionValue === '') {
            return $parentValue !== '';
        }

        return $parentValue === $conditionValue;
    }

    public static function getParentMainIndexFor(array $questions, int $index): ?int
    {
        if (! isset($questions[$index])) {
            return null;
        }

        if (! ($questions[$index]['is_subquestion'] ?? false)) {
            return null;
        }

        for ($i = $index - 1; $i >= 0; $i--) {
            if (($questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }

            return $i;
        }

        return null;
    }

    private static function resolveMainStartIndex(array $questions, int $index): ?int
    {
        if (! isset($questions[$index])) {
            return null;
        }

        if (! ($questions[$index]['is_subquestion'] ?? false)) {
            return $index;
        }

        return self::getParentMainIndexFor($questions, $index);
    }

    private static function getMainBlockEndIndex(array $questions, int $mainStart): ?int
    {
        if (! isset($questions[$mainStart]) || ($questions[$mainStart]['is_subquestion'] ?? false)) {
            return null;
        }

        $end = $mainStart;
        for ($i = $mainStart + 1; $i < count($questions); $i++) {
            if (! ($questions[$i]['is_subquestion'] ?? false)) {
                break;
            }

            $end = $i;
        }

        return $end;
    }

    private static function getPreviousMainQuestionIndex(array $questions, int $index): ?int
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (! ($questions[$i]['is_subquestion'] ?? false)) {
                return $i;
            }
        }

        return null;
    }

    private static function getNextMainQuestionIndex(array $questions, int $index): ?int
    {
        for ($i = $index; $i < count($questions); $i++) {
            if (! ($questions[$i]['is_subquestion'] ?? false)) {
                return $i;
            }
        }

        return null;
    }

    public static function getUsedTriggerValuesForParent(array $questions, int $parentIndex, ?int $ignoreQuestionIndex = null): array
    {
        $used = [];
        foreach ($questions as $index => $question) {
            if ($ignoreQuestionIndex !== null && $index === $ignoreQuestionIndex) {
                continue;
            }

            if (! ($question['is_subquestion'] ?? false)) {
                continue;
            }

            if (self::getParentMainIndexFor($questions, $index) !== $parentIndex) {
                continue;
            }

            $value = trim((string) ($question['condition_value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $used[] = $value;
        }

        return array_values(array_unique($used));
    }

    private static function getTriggerOptionsForQuestion(array $questions, int $questionIndex): array
    {
        if (! isset($questions[$questionIndex])) {
            return [];
        }

        $question = $questions[$questionIndex];
        $type = (string) ($question['question_type'] ?? self::TYPE_YES_NO);

        if ($type === self::TYPE_YES_NO) {
            return [
                ['value' => 'yes', 'label' => trans('service-order::messages.confirm_yes')],
                ['value' => 'no', 'label' => trans('service-order::messages.confirm_no')],
            ];
        }

        if ($type !== self::TYPE_SELECT) {
            return [];
        }

        return collect((array) ($question['options'] ?? []))
            ->map(function (array $option) {
                $key = (string) ($option['key'] ?? '');
                $label = trim((string) ($option['label'] ?? ''));
                if ($key === '' || $label === '') {
                    return null;
                }

                return [
                    'value' => $key,
                    'label' => $label,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
