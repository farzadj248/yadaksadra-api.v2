<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discounts extends Model
{
    use HasFactory;

    /* 
    status
    0.deactive
    1.awating confirmed
    2.confirmed or active
    3.rejected

    type
    1.percent
    2.amount
    
    user_type
    1.Favorite user
    2.all users
    3.normal user
    4.organization
    5.saler
    6.marketer
    
    creator
    1.admin
    2.organization
    */

    protected $fillable = [
        'title','code', 'user_type','type' ,'value' ,'user_limit' ,'products_limit',
        'number_use_limit', 'status', 'expire_date','start_date','rejected_reason',
        'creator','creator_id'
    ];

    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
