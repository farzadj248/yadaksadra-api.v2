<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateHistory extends Model
{
    use HasFactory;
    
    /*
    status
    1.awating
    2.confirmed
    */
    protected $fillable = [
       "uuid",
       "affiliate_id",
       "status"
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
