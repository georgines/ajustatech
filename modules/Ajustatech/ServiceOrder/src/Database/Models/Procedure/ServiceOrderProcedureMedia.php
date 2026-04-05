<?php

namespace Ajustatech\ServiceOrder\Database\Models\Procedure;

use Ajustatech\ServiceOrder\Database\Factories\Procedure\ServiceOrderProcedureMediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderProcedureMedia extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_PDF = 'pdf';

    protected $table = 'service_order_procedure_media';

    protected $fillable = [
        'procedure_id',
        'type',
        'url',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderProcedureMediaFactory::new();
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderProcedure::class, 'procedure_id');
    }
}
