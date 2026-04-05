<?php

namespace Ajustatech\ServiceOrder\Database\Models\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisQuestionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        $questionRows = [];
        $parentMap = [];

        foreach ($questions as $question) {
            $created = static::query()->create([
                'analysis_service_id' => $service->id,
                'parent_question_id' => null,
                'sequence' => (int) ($question['sequence'] ?? 0),
                'section_name' => (string) ($question['section_name'] ?? 'Geral'),
                'question_text' => (string) ($question['question_text'] ?? ''),
                'question_type' => (string) ($question['question_type'] ?? self::TYPE_YES_NO),
                'is_required' => (bool) ($question['is_required'] ?? false),
                'technical_description' => $question['technical_description'] ?? null,
                'is_technical_description_required' => (bool) ($question['is_technical_description_required'] ?? false),
                'images_json' => $question['images_json'] ?? null,
                'is_image_required' => (bool) ($question['is_image_required'] ?? false),
                'required_images_count' => $question['required_images_count'] ?? null,
                'has_help' => (bool) ($question['has_help'] ?? false),
                'help_content' => $question['help_content'] ?? null,
                'options_json' => $question['options_json'] ?? null,
                'condition_value' => $question['condition_value'] ?? null,
                'answer_procedure_map_json' => $question['answer_procedure_map_json'] ?? null,
            ]);

            $clientKey = (string) ($question['client_key'] ?? $created->id);
            $questionRows[$clientKey] = $created;
            $parentMap[$clientKey] = $question['parent_client_key'] ?? null;

        }

        foreach ($parentMap as $clientKey => $parentClientKey) {
            if (!$parentClientKey || !isset($questionRows[$parentClientKey], $questionRows[$clientKey])) {
                continue;
            }

            $questionRows[$clientKey]->update([
                'parent_question_id' => $questionRows[$parentClientKey]->id,
            ]);
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

