<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;
    
    /*
    post_type
    1.product
    2.article
    3.news
    4.video
    
    post_score
    1.positive_score
    2.negative_score
    */
    
    protected $fillable = [
        'user_id','post_type', 'post_id', 'positive_score', 'negative_score'
    ];

 
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
