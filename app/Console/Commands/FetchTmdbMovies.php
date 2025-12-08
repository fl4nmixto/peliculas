<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchTmdbMovies extends Command
{
    protected $signature = 'tmdb:fetch-movies {--path=database/seeders/jsons/tmdb} {--language=es-ES}';

    protected $description = 'Descarga información de películas desde TMDb y la guarda como archivos JSON.';

    protected const MOVIES = [        
        /*
        ['title' => "Che: Un Hombre Nuevo", 'year' => 2010],
        ['title' => "El asadito", 'year' => 2000],
        ['title' => "Whisky", 'year' => 2004],
        ['title' => 'Acné', 'year' => 2008],
        ['title' => 'La vida útil', 'year' => 2010],
        ['title' => "Chile '76", 'year' => 2022],
        ['title' => 'Erdosain', 'year' => 2020],
        ['title' => 'El crítico', 'year' => 2014],
        ['title' => 'El fondo del mar', 'year' => 2003],
        ['title' => 'Una novia errante', 'year' => 2007],
        ['title' => 'Vaquero', 'year' => 2011],
        ['title' => 'Los dueños', 'year' => 2014],
        ['title' => 'Gelbard: la historia secreta del último burgués nacional', 'year' => 2006],
        ['title' => 'Hombre mirando al sudeste', 'year' => 1987],
        ['title' => 'Las puertitas del Sr. López', 'year' => 1988],
        ['title' => 'La clínica del Dr. Cureta', 'year' => 1987],
        ['title' => 'Bolivia', 'year' => 2001],
        ['title' => 'El Abrazo Partido', 'year' => 2004],
        ['title' => 'El asaltante', 'year' => 2007],
        ['title' => 'El hombre de al lado', 'year' => 2009],
        ['title' => 'El suplente', 'year' => 2022],
        ['title' => 'Los delincuentes', 'year' => 2023],
        ['title' => 'Rompecabezas', 'tmdb_id' => 41272],
        ['title' => 'Tomando estado'],
        ['title' => 'Últimas imágenes del naufragio', 'year' => 1989],
        ['title' => 'Distancia de rescate', 'year' => 2021],
        ['title' => 'Los ladrones', 'year' => 2022],
        ['title' => 'Hotel Descanso', 'year' => 2002],
        ['title' => 'Un mundo misterioso', 'year' => 2011],
        ['title' => 'Historias mínimas', 'year' => 2002],
        ['title' => 'El perro', 'year' => 2004],
        ['title' => 'Días de pesca', 'year' => 2012],
        ['title' => 'El otro verano', 'year' => 2018],
        ['title' => 'Derecho de familia', 'year' => 2006],
        ['title' => 'Pajaros volando', 'year' => 2010],
        ['title' => 'El coso', 'year' => 2022],
        /*
        ['title' => 'Valentín', 'year' => 2002],
        ['title' => 'El aura', 'year' => 2005],
        ['title' => 'Infancia clandestina', 'year' => 2012],
        ['title' => 'La deuda interna', 'year' => 1988],
        ['title' => 'La nube', 'year' => 1998],
        ['title' => 'Sur', 'year' => 1988],
        ['title' => 'El exilio de Gardel', 'year' => 1985],
        ['title' => 'Esperando la carroza', 'year' => 1985],
        ['title' => 'El otro', 'year' => 2007],
        ['title' => 'Un oso rojo', 'year' => 2002],
        ['title' => 'Felicidades', 'year' => 2000],
        ['title' => 'Relatos salvajes', 'year' => 2014],
        ['title' => 'Tiempo de valientes', 'year' => 2005],
        ['title' => 'Los guantes mágicos', 'year' => 2003],
        ['title' => 'Silvia Prieto', 'year' => 1999],
        ['title' => 'Los hermanos karaoke'],
        ['title' => 'Buenos Aires Viceversa', 'year' => 1996],
        ['title' => 'Historias extraordinarias', 'year' => 2008],
        ['title' => 'La flor', 'tmdb_id' => 423778],
        ['title' => 'La ciénaga', 'year' => 2001],
        ['title' => 'La niña santa', 'year' => 2004],
        ['title' => 'El estudiante', 'year' => 2011],
        ['title' => 'Sábado', 'year' => 2001],
        ['title' => 'El ángel', 'year' => 2018],
        ['title' => 'Pizza, birra, faso', 'year' => 1998],
        ['title' => 'Mundo grúa', 'year' => 1999],
        ['title' => 'Crónica de una fuga', 'year' => 2006],
        ['title' => 'La historia oficial', 'year' => 1985],
        ['title' => 'Nazareno Cruz y el lobo', 'year' => 1975],
        ['title' => 'Carancho', 'year' => 2010],
        ['title' => 'El bonaerense', 'year' => 2002],
        ['title' => 'El clan', 'year' => 2015],
        ['title' => 'La mirada invisible', 'year' => 2010],
        ['title' => 'El custodio', 'year' => 2006],
        ['title' => 'Las acacias', 'year' => 2011],
        ['title' => 'Sinfonía para Ana', 'year' => 2017],
        ['title' => 'Las mantenidas sin sueños', 'year' => 2005],
        ['title' => 'El Rapto', 'year' => 2023],
        ['title' => 'El rey del once', 'year' => 2016],
        ['title' => 'Masterplan', 'year' => 2012],
        ['title' => 'Al acecho', 'year' => 2020],
        ['title' => 'El futuro que viene', 'year' => 2017],
        ['title' => 'El Pampero', 'year' => 2017],
        ['title' => 'El amor (primera parte)', 'year' => 2005],
        ['title' => 'Cara de queso', 'year' => 2006],
        ['title' => 'Blondi', 'year' => 2023],
        ['title' => 'Medianeras', 'year' => 2011],
        ['title' => 'excursiones', 'year' => 2010],
        ['title' => 'el premio', 'year' => 2011],
        ['title' => 'Sangre en la boca', 'year' => 2016],
        ['title' => 'Todos tenemos un plan', 'year' => 2012],
        */
    ];

    public function handle(): int
    {
        $apiKey = config('services.tmdb.key');

        if (! $apiKey) {
            $this->error('Configura la variable TMDB_API_KEY en tu archivo .env.');
            return self::FAILURE;
        }

        $outputPath = base_path($this->option('path'));
        File::ensureDirectoryExists($outputPath);

        $language = $this->option('language');
        $client = $this->buildClient($apiKey);

        foreach (self::MOVIES as $movieSpec) {
            $title = $movieSpec['title'];
            $this->line("Buscando «{$title}»...");

            $movieId = $this->resolveMovieId($client, $movieSpec, $language);

            if (! $movieId) {
                $this->warn("No se encontró un resultado para {$title}, se omite.");
                continue;
            }

            $details = $this->fetchMovieDetails($client, $movieId, $language);

            if (! $details) {
                $this->warn("No se pudo obtener la información detallada de {$title}.");
                continue;
            }

            $payload = $this->formatPayload($details);
            $filename = $outputPath . '/' . $payload['slug'] . '.json';
            File::put($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info("Guardado: {$filename}");
        }

        $this->info('Descarga finalizada.');
        return self::SUCCESS;
    }

    protected function buildClient(string $apiKey): PendingRequest
    {
        return Http::baseUrl(config('services.tmdb.base_url'))
            ->withQueryParameters(['api_key' => $apiKey])
            ->retry(3, 250);
    }

    protected function resolveMovieId(PendingRequest $client, array $movieSpec, string $language): ?int
    {
        $response = $client->get('search/movie', array_filter([
            'query' => $movieSpec['title'],
            'year' => $movieSpec['year'] ?? null,
            'language' => $language,
            'include_adult' => false,
        ]));

        if ($response->failed()) {
            $this->warn("Error buscando {$movieSpec['title']}: {$response->body()}");
            return null;
        }

        $results = collect($response->json('results', []));

        if ($results->isEmpty()) {
            return null;
        }

        if (isset($movieSpec['year'])) {
            $match = $results->first(function ($result) use ($movieSpec) {
                return Str::of($result['release_date'] ?? '')->startsWith((string) $movieSpec['year']);
            });

            if ($match) {
                return (int) $match['id'];
            }
        }

        return (int) $results->first()['id'];
    }

    protected function fetchMovieDetails(PendingRequest $client, int $movieId, string $language): ?array
    {
        $response = $client->get("movie/{$movieId}", [
            'language' => $language,
            'append_to_response' => 'credits,videos,images',
        ]);

        if ($response->failed()) {
            $this->warn("Error obteniendo detalles del ID {$movieId}: {$response->body()}");
            return null;
        }

        return $response->json();
    }

    protected function formatPayload(array $details): array
    {
        $slug = Str::slug($details['title'] ?? $details['original_title'] ?? 'pelicula');
        $imagesBaseUrl = config('services.tmdb.images_base_url');

        $posterUrl = $this->buildImageUrl($details['poster_path'] ?? null, $imagesBaseUrl);
        $backdropUrl = $this->buildImageUrl($details['backdrop_path'] ?? null, $imagesBaseUrl);

        $trailer = collect(data_get($details, 'videos.results', []))
            ->first(function ($video) {
                return Str::lower($video['site'] ?? '') === 'youtube'
                    && Str::lower($video['type'] ?? '') === 'trailer';
            });

        $cast = collect(data_get($details, 'credits.cast', []))
            ->take(20)
            ->map(function ($person) use ($imagesBaseUrl) {
                return [
                    'id' => $person['id'],
                    'name' => $person['name'],
                    'character' => $person['character'],
                    'order' => $person['order'],
                    'image_url' => $this->buildImageUrl($person['profile_path'] ?? null, $imagesBaseUrl),
                ];
            })
            ->values();

        $crew = collect(data_get($details, 'credits.crew', []))
            ->map(function ($person) use ($imagesBaseUrl) {
                return [
                    'id' => $person['id'],
                    'name' => $person['name'],
                    'department' => $person['department'],
                    'job' => $person['job'],
                    'image_url' => $this->buildImageUrl($person['profile_path'] ?? null, $imagesBaseUrl),
                ];
            })
            ->values();

        $countries = collect($details['production_countries'] ?? []);
        $argentineOnly = $countries->filter(function ($country) {
            return ($country['iso_3166_1'] ?? null) === 'AR';
        });

        if ($argentineOnly->isEmpty()) {
            $argentineOnly = $countries;
        }

        return [
            'source' => 'tmdb',
            'tmdb_id' => $details['id'],
            'slug' => $slug,
            'title' => $details['title'],
            'original_title' => $details['original_title'],
            'overview' => $details['overview'],
            'runtime' => $details['runtime'],
            'release_date' => $details['release_date'],
            'year' => $this->extractYear($details['release_date'] ?? null),
            'tagline' => $details['tagline'],
            'genres' => collect($details['genres'] ?? [])
                ->map(fn ($genre) => Arr::only($genre, ['id', 'name']))
                ->filter(fn ($genre) => isset($genre['name']))
                ->values(),
            'countries' => $argentineOnly->pluck('name')->values(),
            'spoken_languages' => Arr::pluck($details['spoken_languages'] ?? [], 'name'),
            'poster_url' => $posterUrl,
            'backdrop_url' => $backdropUrl,
            'trailer' => $trailer ? 'https://www.youtube.com/watch?v=' . $trailer['key'] : null,
            'cast' => $cast,
            'crew' => $crew,
            'raw' => $details,
        ];
    }

    protected function buildImageUrl(?string $path, string $baseUrl): ?string
    {
        if (! $path) {
            return null;
        }

        return rtrim($baseUrl, '/') . $path;
    }

    protected function extractYear(?string $date): ?int
    {
        if (! $date) {
            return null;
        }

        return (int) Str::substr($date, 0, 4);
    }
}
