<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image_url',
        'arabic_quote',
        'status',
        'is_featured',
        'views',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $base = Str::slug($article->title) . '-' . Str::random(5);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $article->slug = $slug;
            }
            if (empty($article->published_at) && $article->status === 'published') {
                $article->published_at = now();
            }
        });
    }
}
