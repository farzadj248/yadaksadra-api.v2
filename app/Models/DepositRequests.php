<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRequests extends Model
{
    use HasFactory;

    /*
    status
    1.awating
    2.confirmed
    3.rejected
    */
    
    protected $fillable = [
        "transaction_code","user_id", "amount", "status", "file","rejected_reason","shaba_bank"
    ];

    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
