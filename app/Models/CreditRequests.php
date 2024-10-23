<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditRequests extends Model
{
    use HasFactory;

     /*
    status
    1.confirmed
    2.awating
    3.rejected
    */

    protected $fillable = [
        'user_id',
        'subject',
        'status',
        'amount',
        'description'
    ];

    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
