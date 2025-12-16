<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function sources()
    {
        return $this->hasMany(MovieSource::class);
    }

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_sources')
            ->withPivot(['url', 'quality'])
            ->withTimestamps();
    }
}
