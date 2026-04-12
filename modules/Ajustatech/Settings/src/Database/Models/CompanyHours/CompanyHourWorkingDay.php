<?php

namespace Ajustatech\Settings\Database\Models\CompanyHours;

use Ajustatech\Settings\Database\Factories\CompanyHours\CompanyHourWorkingDayFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyHourWorkingDay extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'company_hours_working_days';

    protected $fillable = [
        'company_hour_id',
        'day_key',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return CompanyHourWorkingDayFactory::new();
    }

    public function companyHour(): BelongsTo
    {
        return $this->belongsTo(CompanyHour::class, 'company_hour_id');
    }
}
