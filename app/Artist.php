<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Artist extends Model
{

    protected $dates = ['birth_date'];

    protected $casts = [
        'birth_date' => 'date:d.m.Y'
    ];

    protected $hidden = ['pivot', 'director_id', 'created_at', 'updated_at'];
    protected $fillable = ['artist_type_id', 'poster', 'name', 'surname', 'gender', 'birth_date', 'country', 'bio'];

    public function type()
    {
        return $this->belongsTo(ArtistType::class, 'artist_type_id');
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class)->withTimestamps();
    }

    public function directed()
    {
        return $this->hasMany(Video::class);
    }

    public function getPosterAttribute($value)
    {
        if(strpos($value, 'artist_id_')) { // Works for seeded images and uploaded as well
            return $value;
        } else {
            return url(Storage::url('artistsPosters/' . $value));
        }
    }
}
