<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_no',
        'user_name',
        'mobile',
        'email',
        'stitch_for_name',
        'phone_no',
        'height',
        'body_weight',
        'shoes_size',
        'front_photo',
        'side_photo',
        'back_photo',
        'mesurment_json',
        'additional_requirement',
        'category_id',
        'status',
    ];

    public function categoryStitchItems()
    {
        return $this->hasMany(OrderCategoryStitch::class, 'order_id');
    }
}
