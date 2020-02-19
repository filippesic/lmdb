<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtistType extends Model
{
    public function artists()
    {
        return $this->hasMany(Artist::class);
    }
}
