<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\ServiceOrderAttachmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderAttachment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_attachments';

    protected $fillable = [
        'service_order_id',
        'equipment_type_field_id',
        'field_slug',
        'attachment_type',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function equipmentTypeField(): BelongsTo
    {
        return $this->belongsTo(EquipmentTypeField::class, 'equipment_type_field_id');
    }

    public static function createForOrder(string $serviceOrderId, array $payload): self
    {
        return static::query()->create([
            ...$payload,
            'service_order_id' => $serviceOrderId,
        ]);
    }

    protected static function newFactory()
    {
        return ServiceOrderAttachmentFactory::new();
    }
}
