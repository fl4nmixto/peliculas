<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image_url',
        'bio',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_people')
            ->withPivot('role_id', 'position')
            ->orderBy('movie_people.position')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
