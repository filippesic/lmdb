<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{

    protected $fillable = ['season_number'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

}
