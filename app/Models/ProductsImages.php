<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'url','order'
    ];

    /**
     * datetime cast
     *
     * @var array
     */
    protected $casts = [
        // 'url' => "array",
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
