<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    
    /*
    grade
    1.Main
    2.Custom
    3.Market
    */

    protected $fillable = [
        'uuid',
        'product_id',
        'order_id',
        'user_id',
        'quantity',
        'saved_price',
        'saved_off',
        'grade',
        'user_role',
        'isPriceChanges',
        'status',
        'instock'
    ];

    /**
     * datetime cast
     *
     * @var array
     */
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
