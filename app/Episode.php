<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = ['season_id', 'name'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
