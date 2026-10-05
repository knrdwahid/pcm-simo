<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    /**
     * Disable updated_at since visits are immutable logs.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'session_id',
        'ip_hash',
        'path',
        'url',
        'route_name',
        'page_title',
        'referrer',
        'referrer_type',
        'referrer_host',
        'device_type',
        'browser',
        'platform',
        'user_agent',
        'country',
        'country_code',
        'region',
        'city',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Scope: Visits recorded today.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Visits recorded yesterday.
     */
    public function scopeYesterday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today()->subDay());
    }

    /**
     * Scope: Visits within the past 7 days.
     */
    public function scopeLast7Days(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
    }

    /**
     * Scope: Visits within the past 30 days.
     */
    public function scopeLast30Days(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
    }

    /**
     * Scope: Active online visitors (accessed in the last 5 minutes).
     */
    public function scopeOnline(Builder $query, int $minutes = 5): Builder
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
