<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'order', 'count', 'image_url','parent_id'
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
    
    public function parent(){
        return $this->belongsTo(ProductsCategories::class,'parent_id');
    }
    public function child(){
        return $this->hasMany(ProductsCategories::class,'parent_id');
    }
}
