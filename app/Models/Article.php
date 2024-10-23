<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_body',
        'long_body',
        'category_id',
        'category_title',
        'views',
        'rating',
        'comments_number',
        'image_url',
        'status',
        'tags'
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
        return 'slug';
    }
}
