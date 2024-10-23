<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDefinedCar extends Model
{
    use HasFactory;

    protected $fillable = [
        "car_id", "car_name","company_id","company_name", "model_id", "model_name", "year_id", "year_name", "product_id"
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
