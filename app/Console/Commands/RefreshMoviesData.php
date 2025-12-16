<?php

namespace App\Console\Commands;

use Database\Seeders\MoviesVideoSourcesSeeder;
use Illuminate\Console\Command;

class RefreshMoviesData extends Command
{
    protected $signature = 'movies:refresh-data';

    protected $description = 'Recrear la base de datos y volver a importar todas las películas desde TMDB, CineAR y el seeder de videos.';

    public function handle(): int
    {
        $commands = [
            ['name' => 'migrate:fresh', 'params' => []],
            ['name' => 'tmdb:import-jsons', 'params' => []],
            //['name' => 'cinear:import-jsons', 'params' => []],
            ['name' => 'db:seed', 'params' => ['--class' => MoviesVideoSourcesSeeder::class]],
        ];

        foreach ($commands as $command) {
            $this->info("Ejecutando {$command['name']}...");

            $result = $this->call($command['name'], $command['params']);

            if ($result !== self::SUCCESS) {
                $this->error("El comando {$command['name']} falló. Se detiene la ejecución.");
                return $result;
            }
        }

        $this->info('Base de datos recreada e importaciones completadas correctamente.');

        return self::SUCCESS;
    }
}
