<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $hidden = ['pivot', 'created_at', 'updated_at'];
    protected $fillable = ['name'];

    public function videos()
    {
        return $this->belongsToMany(Video::class)->withTimestamps();
    }
}
