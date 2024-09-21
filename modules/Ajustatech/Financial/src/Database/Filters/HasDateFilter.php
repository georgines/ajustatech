<?php

namespace Ajustatech\Financial\Database\Filters;

trait HasDateFilter
{
    public function scopeToday($query)
    {
        return $query->whereDay('created_at', now()->day);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeThisDate($query, string $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeThisInterval($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
