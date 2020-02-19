<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{

    protected $fillable = ['episodes'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
