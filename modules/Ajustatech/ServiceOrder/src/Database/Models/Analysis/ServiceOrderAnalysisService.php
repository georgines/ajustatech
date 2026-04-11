<?php

namespace Ajustatech\ServiceOrder\Database\Models\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisServiceFactory;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceOrderAnalysisService extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_services';

    protected $fillable = [
        'name',
        'description',
        'value',
        'progress_percentage',
        'last_answered_question_sequence',
        'is_completed',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'progress_percentage' => 'integer',
        'last_answered_question_sequence' => 'integer',
        'is_completed' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ServiceOrderAnalysisServiceFactory::new();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisQuestion::class, 'analysis_service_id')
            ->orderBy('sequence')
            ->orderBy('created_at');
    }

    public static function listForIndex(): Collection
    {
        return static::query()
            ->select(['id', 'name', 'description', 'value', 'created_at'])
            ->withCount('questions')
            ->latest('created_at')
            ->get();
    }

    public static function findWithQuestionsOrFail(string $id): self
    {
        return static::query()
            ->select(['id', 'name', 'description', 'value', 'progress_percentage', 'last_answered_question_sequence', 'is_completed', 'created_at', 'updated_at'])
            ->with([
                'questions' => fn ($query) => $query->select([
                    'id',
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
                    'created_at',
                    'updated_at',
                ]),
            ])
            ->findOrFail($id);
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function listProcedureOptions(): array
    {
        return ServiceOrderProcedure::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    public static function createWithQuestions(array $data, array $questions): self
    {
        return DB::transaction(function () use ($data, $questions) {
            $service = static::query()->create($data);
            ServiceOrderAnalysisQuestion::syncForService($service, $questions);

            return $service;
        });
    }

    public static function createManyWithQuestions(array $items): Collection
    {
        if (empty($items)) {
            return collect();
        }

        return DB::transaction(function () use ($items) {
            $now = now();
            $serviceRows = [];
            $questionSyncPayload = [];

            foreach ($items as $index => $item) {
                $serviceData = (array) ($item['service'] ?? []);
                $serviceId = (string) ($serviceData['id'] ?? Str::uuid());

                $serviceRows[] = [
                    'id' => $serviceId,
                    'name' => (string) ($serviceData['name'] ?? ''),
                    'description' => $serviceData['description'] ?? null,
                    'value' => (float) ($serviceData['value'] ?? 0),
                    'progress_percentage' => (int) ($serviceData['progress_percentage'] ?? 0),
                    'last_answered_question_sequence' => $serviceData['last_answered_question_sequence'] ?? null,
                    'is_completed' => (bool) ($serviceData['is_completed'] ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $questionSyncPayload[] = [
                    'analysis_service_id' => $serviceId,
                    'questions' => (array) ($item['questions'] ?? []),
                ];
            }

            static::query()->insert($serviceRows);
            ServiceOrderAnalysisQuestion::syncManyForServices($questionSyncPayload);

            return static::hydrate($serviceRows);
        });
    }

    public function updateWithQuestions(array $data, array $questions): self
    {
        return DB::transaction(function () use ($data, $questions) {
            $this->update($data);

            $this->questions()->delete();
            ServiceOrderAnalysisQuestion::syncForService($this, $questions);

            return $this;
        });
    }

    public static function updateWithQuestionsById(string $id, array $data, array $questions): self
    {
        return DB::transaction(function () use ($id, $data, $questions) {
            $now = now();

            static::query()
                ->whereKey($id)
                ->update(array_merge($data, ['updated_at' => $now]));

            $service = new static;
            $service->setRawAttributes(['id' => $id] + $data, true);
            $service->exists = true;
            $service->questions()->delete();
            ServiceOrderAnalysisQuestion::syncForService($service, $questions);

            return $service;
        });
    }

    public function deleteWithRelations(): void
    {
        $this->delete();
    }

    public static function deleteById(string $id): int
    {
        return static::query()->whereKey($id)->delete();
    }
}
