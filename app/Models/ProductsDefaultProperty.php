<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsDefaultProperty extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'value',
        'parent_id'
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
