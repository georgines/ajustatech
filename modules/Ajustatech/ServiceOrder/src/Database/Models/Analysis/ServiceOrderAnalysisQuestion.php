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

        if (!empty($rows)) {
            static::query()->insert($rows);
        }
    }

    public function isVisible(array $answersByQuestionId): bool
    {
        if (!$this->parent_question_id) {
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
}

