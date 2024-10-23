<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleComments extends Model
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
        'article_id',
        'user_id',
        'user_name',
        'email',
        'subject',
        'body',
        'status',
        'score',
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
        return $this->hasMany(ArticleComments::class,'reply_id','id');
    }
}
