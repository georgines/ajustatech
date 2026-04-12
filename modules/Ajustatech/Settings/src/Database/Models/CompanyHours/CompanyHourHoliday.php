<?php

namespace Ajustatech\Settings\Database\Models\CompanyHours;

use Ajustatech\Settings\Database\Factories\CompanyHours\CompanyHourHolidayFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyHourHoliday extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'company_hours_holidays';

    protected $fillable = [
        'company_hour_id',
        'holiday_name',
        'holiday_date',
    ];

    protected static function newFactory()
    {
        return CompanyHourHolidayFactory::new();
    }

    public function companyHour(): BelongsTo
    {
        return $this->belongsTo(CompanyHour::class, 'company_hour_id');
    }
}
