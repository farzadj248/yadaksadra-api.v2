<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    /*
    --order status
    0.Awaiting register
    1.Awaiting Payment
    2.Processing
    3.Packing the order
    4.Sending order
    5.order delivery
    6.canceled
    7.rejected
    8.request for rejection

    --post_method
    1.ارسال درون شهری
    2.ارسال برون شهری
    3.بسته بندی و تحویل به باربری
    4.خودم حضوری تحویل می گیرم
    
   --isOfficial
    0.deactive
    1.Awaiting
    2.confirmed
    
    --isRejected
    0.no status
    1.requested from user
    2.confirem from admin
    3.rejected from admin
    4.end time for request
    
    --gateway_pay
    1.wallet
    2.mellat
    3.zarinpal
    4.melli
    5.
    6.credit_purchase
    */

    protected $fillable = [
        'order_code',
        'transactionId',
        'user_id',
        'total',
        'discount',
        'post_method',
        'payment_method',
        'status',
        'address',
        'postal_code',
        'delivery_time',
        'postal_receipt',
        'isRejected',
        'reason_rejection',
        'response_reason_rejection',
        'isOfficial',
        'Official_file',
        'marketer_id',
        'marketer_commission',
        'invited_marketing_id',
        'discount_code_id',
        'discounted_amount',
        'sending_method',
        'sending_amount',
        'gateway_pay',
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
    
    public function getRouteKeyName()
    {
        return 'order_code';
    }
}
