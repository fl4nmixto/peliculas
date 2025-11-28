<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Docuficción',
            'Documental',
            'Drama',
            'Deportes',
            'Comedia negra',
            'Musical',
            'Acción',
            'Suspenso',
            'Thriller',
        ];

        foreach ($genres as $name) {
            Genre::updateOrCreate(
                ['slug' => Str::slug($name, '_')],
                ['name' => $name]
            );
        }
    }
}
