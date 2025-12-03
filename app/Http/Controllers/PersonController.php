<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Person;
use App\Models\Role;
use Illuminate\Support\Collection;

class PersonController extends Controller
{
    public function show(Person $person)
    {
        $person->load(['movies.genres']);
        $this->loadRolesForMovies($person->movies);

        $movies = $person->movies
            ->unique('id')
            ->sortByDesc(function ($movie) {
                return (int) $movie->year ?: 0;
            })
            ->values();

        return view('people.show', [
            'person' => $person,
            'movies' => $movies,
            'genres' => Genre::query()->orderBy('name')->get(),
        ]);
    }

    protected function loadRolesForMovies(Collection $movies): void
    {
        $roleIds = $movies->pluck('pivot.role_id')->filter()->unique();

        if ($roleIds->isEmpty()) {
            return;
        }

        $roles = Role::query()->whereIn('id', $roleIds)->get()->keyBy('id');

        $movies->each(function ($movie) use ($roles) {
            $role = $roles->get($movie->pivot->role_id);

            if ($role) {
                $movie->pivot->setRelation('role', $role);
            }
        });
    }
}
