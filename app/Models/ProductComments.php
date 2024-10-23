<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComments extends Model
{
    use HasFactory;

    /*
    status
    1.awating
    2.confirmed
    3.rejected
    */

    protected $fillable = [
        'reply_id',
        'product_id',
        'user_id',
        'user_name',
        'subject',
        'body',
        'score',
        'positive_score', 
        'negative_score',
        'status',
        'rejected_reason'
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
    
    public function replies()
    {
        return $this->hasMany(ProductComments::class,'reply_id','id');
    }
}
