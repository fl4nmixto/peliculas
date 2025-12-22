<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Command;

class DeleteMovies extends Command
{
    protected $signature = 'movies:delete-selected';

    protected $description = 'Borra (soft delete) las películas listadas en los arrays $tmdbIds o $slugs.';

    /**
     * Identificadores TMDb de las películas a borrar.
     *
     * @var int[]
     */
    protected array $tmdbIds = [
        // Ejemplo: 415533,
    ];

    /**
     * Slugs de las películas a borrar.
     *
     * @var string[]
     */
    protected array $slugs = [
        'el-descanso',
        'cronica-de-una-fuga',
    ];

    public function handle(): int
    {
        $movies = collect();

        if ($this->tmdbIds) {
            $movies = $movies->merge(Movie::whereIn('tmdb_id', $this->tmdbIds)->get());
        }

        if ($this->slugs) {
            $movies = $movies->merge(Movie::whereIn('slug', $this->slugs)->get());
        }

        $movies = $movies->unique('id');

        if ($movies->isEmpty()) {
            $this->warn('No se encontraron películas para borrar. Completa los arrays $tmdbIds o $slugs.');
            return self::SUCCESS;
        }

        $movies->each(function (Movie $movie) {
            $movie->delete();
            $this->info("Película borrada: {$movie->title} (slug: {$movie->slug}, tmdb_id: {$movie->tmdb_id})");
        });

        $this->info('Borrado completado.');

        return self::SUCCESS;
    }
}
