<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCategoryStitch extends Model
{
    protected $fillable = [
        'order_id',
        'category_id',
        'stitch_master_id',
        'stitch_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stitchMaster()
    {
        return $this->belongsTo(User::class, 'stitch_master_id');
    }
}

