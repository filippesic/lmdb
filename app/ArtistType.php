<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtistType extends Model
{

    protected $hidden = ['created_at', 'updated_at'];

    public function artists()
    {
        return $this->hasMany(Artist::class);
    }
}
