<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsBrands extends Model
{
    use HasFactory;

    protected $fillable = [
        'title' ,'parent_id' ,'order', 'count', 'image_url','fa_title'
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
        return $this->belongsTo(ProductsBrands::class,'parent_id');
    }
    public function child(){
        return $this->hasMany(ProductsBrands::class,'parent_id');
    }
}
