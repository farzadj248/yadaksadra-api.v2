<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'province_id', 'province', 'city', 'city_id',
        'address', 'plaque', 'postal_code','floor','building_unit','default'
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
