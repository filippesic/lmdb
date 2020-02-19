<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected  $fillable = ['video_type_id', 'poster', 'name', 'mpaa_rating', 'duration_in_minutes', 'release_date', 'country', 'plot', 'director_id', 'trailer', 'rating'];

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class)->withTimestamps();
    }

    public function type()
    {
        return $this->belongsTo(VideoType::class, 'video_type_id');
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    public function userRating()
    {
        $this->belongsToMany(User::class);
    }

    public function director()
    {
        return $this->belongsTo(Artist::class, 'director_id');
    }

    public function rates()
    {
        return $this->belongsToMany(User::class, 'rates')->withPivot('rate');
    }

    public function getPosterAttribute($value)
    {
        if(strpos($value, 'picsum')) {
            return $value;
        } else {
            return url(Storage::url('videoPosters/' . $value));
        }
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->as('users');
    }
}
