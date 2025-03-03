<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    use HasFactory;

    /*
    status
    0.deactive
    1.active
    
    -- user type --
    1.نمایش برای همه کاربران 
    2. کاربران عادی 
    3. سازمان ها 
    4. فروشندگان لوازم بدکی 
    5. بازاریاب و پخش

    type
    1.Horizontal Slider
    2.Horizontal Banner Slider
    3.Horizontal Banner Header
    4.Horizontal Banner
    5.Horizontal Video
    */

    protected $fillable = [
        'title',
        'image_url',
        'thumbnail',
        'image_link',
        'video_url',
        'expire_date',
        'start_date',
        'type',
        'user_type',
        'status',
        'order'
    ];

    /**
     * datetime cast
     *
     * @var array
     */
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
        'statuc' => 'boolean'
    ];
}
