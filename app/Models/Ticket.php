<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    
    /*
    ticket status
    1.awating user
    2.awating admin
    3.closed
    */

    protected $fillable = [
        'reply_id','ticket_code', 'user_id', 'sender', 'receiver',
        'subject', 'body', 'category_id', 'category_title',
        'status', 'priority','attaches' ,'senderType','rating'
    ];

    /**
     * datetime cast
     *
     * @var array
     */
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
        'sender' => 'array',
        'receiver' => 'array'
    ];
}
