<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /*
    status
    0.active
    1.deactive

    grade
    1.main_price
    2.custom_price
    3.market_price
    
    
    idFreeDelivery
    0.deactive
    1.active
    
    */

    protected $fillable = [
        "market_unique_identifier", "main_unique_identifier", "custom_unique_identifier",
        'commercial_code', 'technical_code',
        'title', 'slug', 'short_body', 'long_body',
        'rating', 'number_sales', 'views','isFreeDelivery',
        'main_price','main_price_2', 'main_price_3',
        'custom_price','custom_price_2','custom_price_3',
        'market_price','market_price_2','market_price_3',
        'category_id', 'category_name','brand_id' ,'brand_name', 
        'country_id' ,'country_name', 'video_url',
        'is_amazing', 'amazing_expire', 'amazing_start','amazing_off',
        'status','tags',
        'main_inventory', 'market_inventory', 'custom_inventory',
        'main_inventory_2', 'market_inventory_2', 'custom_inventory_2',
        'main_inventory_3', 'market_inventory_3', 'custom_inventory_3',
        'main_off', 'market_off', 'custom_off',
        'main_off_2', 'market_off_2', 'custom_off_2',
        'main_off_3', 'market_off_3', 'custom_off_3',
        'main_minimum_purchase', 'market_minimum_purchase', 'custom_minimum_purchase',
        'main_minimum_purchase_2', 'market_minimum_purchase_2', 'custom_minimum_purchase_2',
        'main_minimum_purchase_3', 'market_minimum_purchase_3', 'custom_minimum_purchase_3',
        'isReadyToSend','preparationTime'
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
    
    public function scopeImages()
    {
        return $this->hasOne(ProductsImages::class,"product_id","id");
    }
    
    public function scopeProperties()
    {
        return $this->hasMany(ProductsProperties::class,"product_id","id");
    }

}
