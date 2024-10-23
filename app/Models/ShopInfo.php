<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'terms_and_conditions', 'about', 'briefly_about', 'province', 'provinceId', 'city','cityId','mega_menu',
        'address', 'postal_code','support_phone', 'support_mobile_number', 'whatsapp_number', 'telegram_number', 
        'email','marketer_percent_purchase','other_percent_purchase','catalog','image'
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
