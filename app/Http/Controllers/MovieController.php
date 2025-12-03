<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Role;
use Illuminate\Support\Collection;

class MovieController extends Controller
{
    public function index()
    {
        return $this->renderCatalog();
    }

    public function byGenre(Genre $genre)
    {
        return $this->renderCatalog($genre);
    }

    public function byYear(string $year)
    {
        return $this->renderCatalog(null, $year);
    }

    protected function renderCatalog(?Genre $genre = null, ?string $year = null)
    {
        $query = Movie::query()->with('genres')->orderBy('year', 'desc');

        if ($genre) {
            $query->whereHas('genres', fn ($builder) => $builder->where('genres.id', $genre->id));
        }

        if ($year) {
            $query->where('year', $year);
        }

        return view('welcome', [
            'movies' => $query->get(),
            'genres' => Genre::query()->orderBy('name')->get(),
            'currentGenre' => $genre,
            'currentYear' => $year,
        ]);
    }

    public function show(Movie $movie)
    {
        $movie->load(['genres', 'sources.provider', 'people', 'galleryImages']);
        $this->loadRolesForPeople($movie->people);
        $credits = $this->splitCredits($movie->people);

        return view('movies.show', [
            'movie' => $movie,
            'genres' => Genre::query()->orderBy('name')->get(),
            'credits' => $credits,
        ]);
    }

    protected function loadRolesForPeople(Collection $people): void
    {
        $roleIds = $people->pluck('pivot.role_id')->filter()->unique();

        if ($roleIds->isEmpty()) {
            return;
        }

        $roles = Role::query()->whereIn('id', $roleIds)->get()->keyBy('id');

        $people->each(function ($person) use ($roles) {
            $role = $roles->get($person->pivot->role_id);

            if ($role) {
                $person->pivot->setRelation('role', $role);
            }
        });
    }

    protected function splitCredits(Collection $people): array
    {
        return [
            'directors' => $people->filter(function ($person) {
                return optional($person->pivot->role)->code === 'director';
            })->values(),
            'featuredCast' => $people->filter(function ($person) {
                $role = optional($person->pivot->role);
                return $role && $role->category === 'cast' && $role->is_featured;
            })->values(),
            'cast' => $people->filter(function ($person) {
                $role = optional($person->pivot->role);
                return $role && $role->category === 'cast' && ! $role->is_featured;
            })->values(),
            'crew' => $people->filter(function ($person) {
                $role = optional($person->pivot->role);

                if (! $role) {
                    return false;
                }

                if ($role->category === 'crew') {
                    return true;
                }

                return $role->category === 'director' && $role->code !== 'director';
            })->values(),
        ];
    }
}
