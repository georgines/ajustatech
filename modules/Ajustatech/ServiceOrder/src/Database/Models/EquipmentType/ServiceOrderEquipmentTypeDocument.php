<?php

namespace Ajustatech\ServiceOrder\Database\Models\EquipmentType;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentType\ServiceOrderEquipmentTypeDocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeDocument extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_FIXED_PDF = 'fixed_pdf';
    public const TYPE_EDITABLE_TEMPLATE = 'editable_template';

    protected $table = 'service_order_equipment_type_documents';

    protected $fillable = [
        'equipment_type_id',
        'document_type',
        'title',
        'description',
        'template_content',
        'variables_json',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'variables_json' => 'array',
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderEquipmentTypeDocumentFactory::new();
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentType::class, 'equipment_type_id');
    }

    public static function createManyForEquipmentType(string $equipmentTypeId, array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $rows = collect($documents)
            ->map(function (array $document, int $index) use ($equipmentTypeId) {
                return [
                    'id' => (string) Str::uuid(),
                    'equipment_type_id' => $equipmentTypeId,
                    'document_type' => $document['document_type'],
                    'title' => $document['title'],
                    'description' => $document['description'] ?? null,
                    'template_content' => $document['template_content'] ?? null,
                    'variables_json' => isset($document['variables_json']) ? json_encode($document['variables_json']) : null,
                    'disk' => $document['disk'] ?? null,
                    'path' => $document['path'] ?? null,
                    'original_name' => $document['original_name'] ?? null,
                    'mime_type' => $document['mime_type'] ?? null,
                    'extension' => $document['extension'] ?? null,
                    'size' => $document['size'] ?? null,
                    'sort_order' => $document['sort_order'] ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        static::query()->insert($rows);
    }
}
