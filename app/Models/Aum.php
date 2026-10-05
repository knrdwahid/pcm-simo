<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aum extends Model
{
    use HasFactory;

    /**
     * Jenis entitas organisasi yang dapat dikelola dari dashboard.
     */
    public const TYPES = [
        'organisasi' => 'Majelis & Lembaga',
        'ortom' => 'Organisasi Otonom (Ortom)',
        'amal_usaha' => 'Amal Usaha',
        'masjid' => 'Masjid & Musholla',
    ];

    protected $fillable = [
        'name',
        'type',
        'category',
        'icon',
        'address',
        'leader',
        'phone',
        'website',
        'image_url',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
