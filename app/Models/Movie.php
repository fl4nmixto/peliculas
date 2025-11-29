<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function getTrailerEmbedUrlAttribute(): ?string
    {
        if (! $this->trailer_url) {
            return null;
        }

        $url = $this->trailer_url;
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        if (Str::contains($host, ['youtube.com'])) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            $videoId = $query['v'] ?? null;

            if ($videoId) {
                return 'https://www.youtube.com/embed/' . $videoId;
            }
        }

        if (Str::contains($host, ['youtu.be'])) {
            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

            if ($path) {
                return 'https://www.youtube.com/embed/' . $path;
            }
        }

        return $url;
    }
}
