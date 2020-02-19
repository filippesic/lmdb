<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoType extends Model
{

    protected $hidden = ['created_at', 'updated_at'];

    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }
}
