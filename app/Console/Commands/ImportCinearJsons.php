<?php

namespace App\Console\Commands;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Role;
use App\Models\MovieSource;
use App\Models\Provider;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportCinearJsons extends Command
{
    protected $signature = 'cinear:import-jsons {path=database/seeders/jsons/cinear} {--dry-run}';

    protected $description = 'Importa películas desde archivos JSON descargados de CINE.AR';

    protected const PLACEHOLDER_AVATAR_IDS = [
        '561d5f562916c53546d2bd0d',
    ];

    protected const ROLE_ALIAS = [
        'protagonistas' => 'cast-featured',
        'elenco-secundario' => 'cast',
        'direccion' => 'director',
        'director' => 'director',
        'direccion-general' => 'director',
        'codireccion' => 'director',
    ];

    protected const CANONICAL_ROLES = [
        'cast-featured' => [
            'name' => 'Protagonistas',
            'category' => 'cast',
            'is_featured' => true,
            'position' => 1,
        ],
        'cast' => [
            'name' => 'Elenco',
            'category' => 'cast',
            'is_featured' => false,
            'position' => 10,
        ],
        'director' => [
            'name' => 'Dirección',
            'category' => 'director',
            'is_featured' => true,
            'position' => 0,
        ],
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! File::exists($path)) {
            $this->error("La ruta {$path} no existe.");
            return self::FAILURE;
        }

        $files = collect(File::files($path))->filter(function ($file) {
            return Str::of($file->getFilename())->lower()->endsWith('.json');
        });

        if ($files->isEmpty()) {
            $this->warn('No se encontraron archivos JSON en la ruta proporcionada.');
            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');

        foreach ($files as $file) {
            $this->line("Procesando {$file->getFilename()}...");
            $data = json_decode(File::get($file), true);

            if (! is_array($data)) {
                $this->error('El archivo no contiene JSON válido, se omite.');
                continue;
            }

            if ($dryRun) {
                $this->table(
                    ['Título', 'Año', 'Duración', 'Afiches', 'Personas'],
                    [[
                        Arr::get($data, 'tit'),
                        Arr::get($data, 'an'),
                        Arr::get($data, 'dura'),
                        count(Arr::get($data, 'afis', [])),
                        count(Arr::get($data, 'pers.01', [])) + count(Arr::get($data, 'pers.02', [])),
                    ]]
                );

                continue;
            }

            $this->importMovie($data);
        }

        $this->info('Importación finalizada.');

        return self::SUCCESS;
    }

    protected function importMovie(array $data): void
    {
        $title = Arr::get($data, 'tit');
        $slug = Str::slug($title);

        if (! $title || ! $slug) {
            $this->error('El archivo no tiene título, se omite.');
            return;
        }

        $movie = Movie::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'synopsis' => Arr::get($data, 'sino'),
                'duration' => Arr::get($data, 'dura'),
                'year' => Arr::get($data, 'an'),
                'rating' => $this->ratingFromTags(Arr::get($data, 'tags', [])),
                'score' => (int) round(Arr::get($data, 'rProme', 0)),
                'image_url' => $this->buildPosterUrl($data),
                'trailer_url' => $this->buildTrailerUrl($data),
            ]
        );

        $genreIds = collect(Arr::get($data, 'gens', []))
            ->pluck('nom')
            ->filter()
            ->map(function ($name) {
                $slug = Str::slug($name);
                return Genre::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            })
            ->all();

        if (! empty($genreIds)) {
            $movie->genres()->sync($genreIds);
        }

        $movie->people()->detach();

        foreach (['pers.01', 'pers.02'] as $groupKey) {
            $people = Arr::get($data, $groupKey, []);

            foreach ($people as $personData) {
                $person = $this->persistPerson($personData, Arr::get($data, 'objsturi'));

                if (! $person) {
                    continue;
                }

                $role = $this->resolveRole($personData);
                $position = $this->positionFromRolorden(Arr::get($personData, 'rolorden'));

                $movie->people()->attach($person->id, [
                    'role_id' => $role->id,
                    'position' => $position,
                ]);
            }
        }

        $this->attachMovieSource($movie, $data);
    }

    protected function ratingFromTags(array $tags): ?string
    {
        return collect($tags)
            ->first(fn ($tag) => Str::startsWith($tag, 'sam'));
    }

    protected function attachMovieSource(Movie $movie, array $data): void
    {
        $sourceId = Arr::get($data, 'id.sid');

        if (! $sourceId) {
            return;
        }

        $url = 'https://play.cine.ar/INCAA/produccion/' . $sourceId;

        $provider = Provider::firstOrCreate(
            ['slug' => 'cine-ar'],
            ['name' => 'CINE.AR']
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

    protected function buildPosterUrl(array $data): ?string
    {
        $base = Arr::get($data, 'objsturi');
        $first = Arr::get($data, 'afis.0');

        if ($base && $first) {
            return rtrim($base, '/') . '/' . $first . '/context/odeon_afiche_prod';
        }

        return null;
    }

    protected function buildAvatarUrl(?string $base, ?string $avatar): ?string
    {
        if (! $base || ! $avatar) {
            return null;
        }

        if (in_array($avatar, self::PLACEHOLDER_AVATAR_IDS, true)) {
            return null;
        }

        return rtrim($base, '/') . '/' . $avatar . '/context/avatar';
    }

    protected function buildTrailerUrl(array $data): ?string
    {
        return Arr::get($data, 'trailer')
            ?? Arr::get($data, 'playeruri')
            ?? Arr::get($data, 'playerurimobile');
    }

    protected function persistPerson(array $data, ?string $imageBase): ?Person
    {
        $name = Arr::get($data, 'nom');

        if (! $name) {
            return null;
        }

        $person = Person::firstOrNew(['slug' => Str::slug($name)]);
        $person->name = $name;

        $imageUrl = $this->buildAvatarUrl($imageBase, Arr::get($data, 'avatar'));

        if ($imageUrl) {
            $person->image_url = $imageUrl;
        }

        $person->save();

        return $person;
    }

    protected function resolveRole(array $data): Role
    {
        $code = $this->normalizeRole($data);
        $canonicalCode = self::ROLE_ALIAS[$code] ?? $code;

        if (isset(self::CANONICAL_ROLES[$canonicalCode])) {
            return Role::updateOrCreate(
                ['code' => $canonicalCode],
                self::CANONICAL_ROLES[$canonicalCode]
            );
        }

        $name = Arr::get($data, 'roldesc') ?? Arr::get($data, 'rol') ?? Str::headline($code);

        return Role::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'category' => $this->determineRoleCategory($data),
                'is_featured' => $this->isFeaturedRole($data),
                'position' => $this->positionFromRolorden(Arr::get($data, 'rolorden')),
            ]
        );
    }

    protected function normalizeRole(array $data): string
    {
        return Str::slug(Arr::get($data, 'roldesc') ?? Arr::get($data, 'rol') ?? 'rol');
    }

    protected function determineRoleCategory(array $data): string
    {
        $group = Arr::get($data, 'grupo');
        $roleCode = Str::lower((string) Arr::get($data, 'rol'));
        $roleDescription = Str::lower((string) Arr::get($data, 'roldesc'));
        $roleSlug = Str::slug((string) Arr::get($data, 'roldesc', ''));

        $specialCrew = [
            'direccion-de-fotografia',
            'direccion-de-sonido',
            'asistente-de-direccion',
            'direccion-de-arte',
        ];

        if (in_array($roleSlug, $specialCrew, true)) {
            return 'crew';
        }

        $directorCodes = ['dir', 'codir'];
        $directorSlugs = ['direccion', 'direccion-general', 'director', 'codireccion'];

        if (in_array($roleCode, $directorCodes, true) || in_array($roleSlug, $directorSlugs, true) || Str::contains($roleDescription, 'dirección general')) {
            return 'director';
        }

        if ($group === '02') {
            return 'crew';
        }

        return 'cast';
    }

    protected function isFeaturedRole(array $data): bool
    {
        $roleCode = Str::lower((string) Arr::get($data, 'rol'));
        $roleDescription = Str::lower((string) Arr::get($data, 'roldesc'));

        if (Str::contains($roleDescription, 'protagonista')) {
            return true;
        }

        return in_array($roleCode, ['int', 'pro', 'prg'], true);
    }

    protected function positionFromRolorden(?string $rolorden): int
    {
        if (! $rolorden) {
            return 0;
        }

        return (int) ltrim($rolorden, '0') ?: 0;
    }
}
