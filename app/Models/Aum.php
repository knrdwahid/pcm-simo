<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'address',
        'leader',
        'phone',
        'image_url',
        'description',
    ];
}
