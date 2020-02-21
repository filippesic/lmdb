<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = ['user_id', 'video_id'];

    protected $hidden = ['created_at', 'updated_at'];

}
