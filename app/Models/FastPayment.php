<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastPayment extends Model
{
    use HasFactory;
    
    /*
        status
        1.awating
        2.confirmed
        3.cancelled
    */

    protected $fillable = [
        "full_name", "amount", "email", "mobile_number", "address", "description", "status",
        'SaleReferenceId', 'SaleOrderId', 'CardHolderPan', 'gateway_pay','gateway_title'
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
