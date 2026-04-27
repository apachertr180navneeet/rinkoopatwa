<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'measurements',
        'youtube_url',
        'image'
    ];

    protected $casts = [
        'measurements' => 'string',
    ];

    public function getMeasurementsArrayAttribute(): array
    {
        if (!$this->measurements) {
            return [];
        }

        return array_values(
            array_filter(
                array_map('trim', explode(',', $this->measurements)),
                fn ($item) => $item !== ''
            )
        );
    }

    public function stitches()
    {
        return $this->belongsToMany(User::class, 'category_stitch', 'category_id', 'stitch_id')
            ->withTimestamps();
    }
}

