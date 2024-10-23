<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable=[
        "avatar",
        "full_name",
        "job_position",
        "body",
        "sort"
    ];
}
