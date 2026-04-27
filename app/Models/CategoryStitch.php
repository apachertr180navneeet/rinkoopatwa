<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryStitch extends Model
{
    use HasFactory;

    protected $table = 'category_stitch';

    protected $fillable = [
        'order_id',
        'category_id',
        'stitch_id',
        'status',
    ];
}