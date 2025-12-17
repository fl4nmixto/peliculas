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
        'original_title',
        'tmdb_id',
        'tagline',
        'synopsis',
        'release_date',
        'duration',
        'year',
        'rating',
        'score',
        'image_url',
        'backdrop_url',
        'countries',
        'spoken_languages',
        'trailer_url',
    ];

    protected $casts = [
        'release_date' => 'date',
        'countries' => 'array',
        'spoken_languages' => 'array',
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

    public function getPreferredVideoSourceAttribute(): ?MovieSource
    {
        return $this->sources
            ->filter(fn ($source) => optional($source->provider)->slug !== 'cinear')
            ->filter(fn ($source) => filled($source->url))
            ->sort(function ($a, $b) {
                $qualityComparison = ($b->quality ?? 0) <=> ($a->quality ?? 0);

                if ($qualityComparison !== 0) {
                    return $qualityComparison;
                }

                $aIsOkRu = optional($a->provider)->slug === 'ok-ru';
                $bIsOkRu = optional($b->provider)->slug === 'ok-ru';

                if ($aIsOkRu === $bIsOkRu) {
                    return 0;
                }

                return $aIsOkRu ? -1 : 1;
            })
            ->first();
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        $source = $this->preferred_video_source;

        if (! $source) {
            return null;
        }

        return $this->buildEmbedUrlForSource($source);
    }

    public function getOkRuEmbedUrlAttribute(): ?string
    {
        $source = $this->sources
            ->first(fn ($source) => optional($source->provider)->slug === 'ok-ru');

        if (! $source) {
            return null;
        }

        return $this->buildOkRuEmbedUrl($source->url);
    }

    protected function buildEmbedUrlForSource(MovieSource $source): ?string
    {
        $slug = optional($source->provider)->slug;

        return match ($slug) {
            'ok-ru' => $this->buildOkRuEmbedUrl($source->url),
            'youtube' => $this->buildYoutubeEmbedUrl($source->url),
            'vimeo' => $this->buildVimeoEmbedUrl($source->url),
            default => $source->url,
        };
    }

    protected function buildOkRuEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $videoId = Str::of($path)->afterLast('/')->before('?')->before('#')->trim();

        if ($videoId->isEmpty()) {
            return null;
        }

        return 'https://ok.ru/videoembed/' . $videoId;
    }

    protected function buildYoutubeEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

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

    protected function buildVimeoEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? '';

        if (Str::contains($host, ['player.vimeo.com'])) {
            return $url;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path) {
            $videoId = Str::of($path)->afterLast('/')->trim();

            if ($videoId->isNotEmpty()) {
                return 'https://player.vimeo.com/video/' . $videoId;
            }
        }

        return $url;
    }
}
