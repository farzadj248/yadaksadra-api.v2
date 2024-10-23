<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    
    /*
    status
    1.awating
    2.confirmed
    3.cancelled
    
    --type
    1.orders
    2.other
    */

    protected $fillable = [
        'user_id', 'order_id', 'description', 'SaleReferenceId',
        'SaleOrderId', 'CardHolderPan', 'gateway_pay', 'type', 'amount', 'status'
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
