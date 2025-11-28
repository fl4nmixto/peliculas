<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'synopsis',
        'duration',
        'year',
        'rating',
        'score',
        'image_url',
        'trailer_url',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    public function sources()
    {
        return $this->hasMany(MovieSource::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class, 'movie_people')
            ->withPivot('role_id', 'position')
            ->orderBy('movie_people.position')
            ->withTimestamps();
    }

    public function galleryImages()
    {
        return $this->hasMany(MovieGalleryImage::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
