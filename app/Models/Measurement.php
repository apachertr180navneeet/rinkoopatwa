<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Measurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'remark',
        'video_link',
        'status',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_measurement', 'measurement_id', 'category_id');
    }
}
