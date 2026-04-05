<?php

namespace Ajustatech\ServiceOrder\Database\Models\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisServiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
            ->withCount('questions')
            ->latest()
            ->get();
    }

    public static function findWithQuestionsOrFail(string $id): self
    {
        return static::query()
            ->with([
                'questions',
            ])
            ->findOrFail($id);
    }

    public static function createWithQuestions(array $data, array $questions): self
    {
        return DB::transaction(function () use ($data, $questions) {
            $service = static::query()->create($data);
            ServiceOrderAnalysisQuestion::syncForService($service, $questions);

            return $service->load('questions');
        });
    }

    public function updateWithQuestions(array $data, array $questions): self
    {
        return DB::transaction(function () use ($data, $questions) {
            $this->update($data);

            $this->questions()->delete();
            ServiceOrderAnalysisQuestion::syncForService($this, $questions);

            return $this->load('questions');
        });
    }

    public function deleteWithRelations(): void
    {
        $this->delete();
    }
}

