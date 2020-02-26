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

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

}
