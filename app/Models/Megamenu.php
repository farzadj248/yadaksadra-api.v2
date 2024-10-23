<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Megamenu extends Model
{
    use HasFactory;
    
    /*
    item type
    1.products
    2.category
    3.brands
    4.cars
    5.country
    */
    
    protected $fillable=[
        'title','image', 'level', 'link', 'parent_id'
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
