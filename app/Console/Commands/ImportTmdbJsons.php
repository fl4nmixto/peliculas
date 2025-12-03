<?php

namespace App\Console\Commands;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\MovieSource;
use App\Models\Person;
use App\Models\Provider;
use App\Models\Role;
use App\Support\RoleCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportTmdbJsons extends Command
{
    protected $signature = 'tmdb:import-jsons {path=database/seeders/jsons/tmdb} {--dry-run}';

    protected $description = 'Importa películas desde archivos JSON generados a partir de TMDb';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! File::exists($path)) {
            $this->error("La ruta {$path} no existe.");
            return self::FAILURE;
        }

        if (File::isFile($path)) {
            if (! Str::of($path)->lower()->endsWith('.json')) {
                $this->warn("El archivo {$path} no es un JSON.");
                return self::SUCCESS;
            }
            $files = collect([$path]);
        } else {
            $files = collect(File::files($path))
                ->map(fn ($file) => $file->getPathname())
                ->filter(fn ($filePath) => Str::of($filePath)->lower()->endsWith('.json'));
        }

        if ($files->isEmpty()) {
            $this->warn('No se encontraron archivos JSON en la ruta proporcionada.');
            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');

        foreach ($files as $filePath) {
            $this->line("Procesando " . basename($filePath) . "...");
            $data = json_decode(File::get($filePath), true);

            if (! is_array($data)) {
                $this->error('El archivo no contiene JSON válido, se omite.');
                continue;
            }

            if ($dryRun) {
                $this->table(
                    ['Título', 'Año', 'Duración', 'Elenco', 'Equipo'],
                    [[
                        Arr::get($data, 'title'),
                        Arr::get($data, 'year'),
                        Arr::get($data, 'runtime'),
                        count(Arr::get($data, 'cast', [])),
                        count(Arr::get($data, 'crew', [])),
                    ]]
                );
                continue;
            }

            $this->importMovie($data);
        }

        $this->info('Importación desde TMDb finalizada.');

        return self::SUCCESS;
    }

    protected function importMovie(array $data): void
    {
        $title = Arr::get($data, 'title');
        $slug = Str::slug(Arr::get($data, 'slug') ?? $title);

        if (! $title || ! $slug) {
            $this->error('El archivo no tiene título válido, se omite.');
            return;
        }

        $movie = Movie::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'original_title' => Arr::get($data, 'original_title'),
                'tmdb_id' => Arr::get($data, 'tmdb_id'),
                'tagline' => Arr::get($data, 'tagline'),
                'synopsis' => Arr::get($data, 'overview'),
                'release_date' => Arr::get($data, 'release_date'),
                'duration' => Arr::get($data, 'runtime'),
                'year' => Arr::get($data, 'year'),
                'rating' => null,
                'score' => $this->scoreFromVoteAverage(Arr::get($data, 'raw.vote_average')),
                'image_url' => Arr::get($data, 'poster_url'),
                'backdrop_url' => Arr::get($data, 'backdrop_url'),
                'countries' => Arr::get($data, 'countries', []),
                'spoken_languages' => Arr::get($data, 'spoken_languages', []),
                'trailer_url' => $this->resolveTrailerUrl($data),
            ]
        );

        $this->syncGenres($movie, Arr::get($data, 'genres', []));

        $movie->people()->detach();

        $this->attachCast($movie, Arr::get($data, 'cast', []));
        $this->attachCrew($movie, Arr::get($data, 'crew', []));

        $this->syncGalleryImages($movie, $data);

        $this->attachTmdbSource($movie, Arr::get($data, 'tmdb_id'));
        $this->attachOkRuSource($movie, Arr::get($data, 'okru_url'));
    }

    protected function syncGenres(Movie $movie, array $genres): void
    {
        $genreIds = collect($genres)
            ->map(function ($genre) {
                $tmdbId = null;
                $name = null;

                if (is_array($genre)) {
                    $tmdbId = Arr::get($genre, 'id');
                    $name = Arr::get($genre, 'name');
                } else {
                    $name = $genre;
                }

                if (! $name) {
                    return null;
                }

                $slug = Str::slug($name);

                $existing = null;

                if ($tmdbId) {
                    $existing = Genre::query()->where('tmdb_id', $tmdbId)->first();
                }

                if (! $existing) {
                    $existing = Genre::query()->where('slug', $slug)->first();
                }

                if (! $existing) {
                    $existing = new Genre();
                }

                if ($tmdbId) {
                    $existing->tmdb_id = $tmdbId;
                }

                $existing->name = $name;
                $existing->slug = $slug;
                $existing->save();

                return $existing->id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! empty($genreIds)) {
            $movie->genres()->sync($genreIds);
        }
    }

    protected function attachCast(Movie $movie, array $cast): void
    {
        foreach ($cast as $index => $personData) {
            $person = $this->persistPerson($personData);

            if (! $person) {
                continue;
            }

            $role = $this->resolveCastRole($index);

            $movie->people()->attach($person->id, [
                'role_id' => $role->id,
                'position' => $index + 1,
            ]);
        }
    }

    protected function attachCrew(Movie $movie, array $crew): void
    {
        $uniqueCrew = collect($crew)
            ->unique(function ($member) {
                return Arr::get($member, 'id') . '-' . Arr::get($member, 'job');
            })
            ->values()
            ->take(150);

        $offset = 100;

        foreach ($uniqueCrew as $index => $member) {
            $person = $this->persistPerson($member);

            if (! $person) {
                continue;
            }

            $role = $this->resolveCrewRole($member);

            $movie->people()->attach($person->id, [
                'role_id' => $role->id,
                'position' => min($offset + $index, 255),
            ]);
        }
    }

    protected function persistPerson(array $data): ?Person
    {
        $name = Arr::get($data, 'name');

        if (! $name) {
            return null;
        }

        $person = Person::firstOrNew(['slug' => Str::slug($name)]);
        $person->name = $name;

        $imageUrl = Arr::get($data, 'image_url');

        if ($imageUrl) {
            $person->image_url = $imageUrl;
        }

        $person->save();

        return $person;
    }

    protected function resolveCastRole(int $index): Role
    {
        $isFeatured = $index < 2;
        $code = $isFeatured ? 'cast-featured' : 'cast';

        return Role::updateOrCreate(
            ['code' => $code],
            [
                'name' => $isFeatured ? 'Protagonistas' : 'Elenco',
                'category' => 'cast',
                'is_featured' => $isFeatured,
                'position' => $isFeatured ? 1 : 10,
            ]
        );
    }

    protected function resolveCrewRole(array $member): Role
    {
        $job = Arr::get($member, 'job') ?: Arr::get($member, 'department') ?: 'Equipo';
        $code = Str::slug($job);
        $category = $this->determineCrewCategory($job, Arr::get($member, 'department'));

        if ($match = RoleCatalog::match($code, $job)) {
            return Role::updateOrCreate(
                ['code' => $match['code']],
                $match['attributes']
            );
        }

        return Role::updateOrCreate(
            ['code' => $code],
            [
                'name' => $job,
                'category' => $category,
                'is_featured' => $category === 'director',
                'position' => $this->rolePosition($category),
            ]
        );
    }

    protected function determineCrewCategory(?string $job, ?string $department): string
    {
        $job = Str::lower($job ?? '');
        $department = Str::lower($department ?? '');

        if (Str::contains($job, 'director') || $department === 'directing') {
            return 'director';
        }

        if ($department === 'crew') {
            return 'crew';
        }

        return 'crew';
    }

    protected function rolePosition(string $category): int
    {
        return match ($category) {
            'director' => 0,
            'cast' => 10,
            default => 20,
        };
    }

    protected function syncGalleryImages(Movie $movie, array $data): void
    {
        $baseUrl = config('services.tmdb.images_base_url');

        if (! $baseUrl) {
            return;
        }

        $backdrops = collect(Arr::get($data, 'raw.images.backdrops', []));
        $posters = collect(Arr::get($data, 'raw.images.posters', []));

        $images = $backdrops->isNotEmpty() ? $backdrops : $posters;

        $gallery = $images
            ->map(fn ($image) => $image['file_path'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($path) => rtrim($baseUrl, '/') . $path)
            ->take(12);

        $movie->galleryImages()->delete();

        if ($gallery->isEmpty()) {
            return;
        }

        $movie->galleryImages()->createMany(
            $gallery->map(fn ($url, $index) => [
                'image_url' => $url,
                'position' => $index + 1,
            ])
            ->all()
        );
    }

    protected function attachTmdbSource(Movie $movie, ?int $tmdbId): void
    {
        if (! $tmdbId) {
            return;
        }

        $provider = Provider::firstOrCreate(
            ['slug' => 'tmdb'],
            ['name' => 'TMDb']
        );

        MovieSource::updateOrCreate(
            [
                'movie_id' => $movie->id,
                'provider_id' => $provider->id,
            ],
            [
                'url' => 'https://www.themoviedb.org/movie/' . $tmdbId,
            ]
        );
    }

    protected function attachOkRuSource(Movie $movie, ?string $url): void
    {
        if (! $url) {
            return;
        }

        $provider = Provider::firstOrCreate(
            ['slug' => 'ok-ru'],
            ['name' => 'ok.ru']
        );

        MovieSource::updateOrCreate(
            [
                'movie_id' => $movie->id,
                'provider_id' => $provider->id,
            ],
            [
                'url' => $url,
            ]
        );
    }

    protected function scoreFromVoteAverage($value): int
    {
        $vote = (float) $value;

        if ($vote <= 0) {
            return 0;
        }

        return (int) round($vote / 2);
    }

    protected function resolveTrailerUrl(array $data): ?string
    {
        $trailer = Arr::get($data, 'trailer');

        if ($trailer) {
            return $trailer;
        }

        $videos = collect(Arr::get($data, 'videos.results', []));

        if ($videos->isEmpty()) {
            $videos = collect(Arr::get($data, 'raw.videos.results', []));
        }

        $match = $videos->first(function ($video) {
            return Str::lower($video['site'] ?? '') === 'youtube'
                && Str::lower($video['type'] ?? '') === 'trailer';
        }) ?? $videos->first(function ($video) {
            return Str::lower($video['site'] ?? '') === 'youtube';
        });

        if (! $match || empty($match['key'])) {
            return null;
        }

        return 'https://www.youtube.com/watch?v=' . $match['key'];
    }
}
