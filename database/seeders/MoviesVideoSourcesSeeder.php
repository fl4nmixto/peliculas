<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Provider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MoviesVideoSourcesSeeder extends Seeder
{
    private string $jsonPath = 'database/seeders/jsons/movies_video_sources.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = $this->loadJson();

        if (empty($records)) {
            return;
        }

        $providers = $this->ensureProviders();

        foreach ($records as $record) {
            $slug = $record['slug'] ?? null;

            if (! $slug) {
                continue;
            }

            $movie = Movie::where('slug', $slug)->first();

            if (! $movie) {
                $this->command?->warn("Movie with slug '{$slug}' not found");
                continue;
            }

            $this->updateTrailer($movie, $record['youtube_trailer'] ?? null);
            $this->updateSource($movie, $providers['ok-ru'], $record['ok_ru_id'] ?? null, $record['ok_ru_quality'] ?? null);
            $this->updateSource($movie, $providers['youtube'], $record['youtube_id'] ?? null, $record['youtube_quality'] ?? null);
        }
    }

    private function loadJson(): array
    {
        $path = base_path($this->jsonPath);

        if (! File::exists($path)) {
            $this->command?->error("Movies video sources file not found at {$path}");
            return [];
        }

        $content = File::get($path);
        $records = json_decode($content, true);

        if (! is_array($records)) {
            $this->command?->error('Movies video sources file has invalid JSON.');
            return [];
        }

        return $records;
    }

    private function ensureProviders(): array
    {
        return [
            'ok-ru' => Provider::firstOrCreate(
                ['slug' => 'ok-ru'],
                ['name' => 'OK.ru']
            ),
            'youtube' => Provider::firstOrCreate(
                ['slug' => 'youtube'],
                ['name' => 'YouTube']
            ),
        ];
    }

    private function updateTrailer(Movie $movie, ?string $youtubeKey): void
    {
        if ($movie->trailer_url || ! $youtubeKey) {
            return;
        }

        $movie->update([
            'trailer_url' => $this->buildYoutubeUrl($youtubeKey),
        ]);
    }

    private function updateSource(Movie $movie, Provider $provider, ?string $videoId, ?int $quality): void
    {
        if (! $videoId) {
            return;
        }

        $source = $movie->sources()->firstOrNew(['provider_id' => $provider->id]);
        $source->url = $provider->slug === 'ok-ru'
            ? $this->buildOkRuUrl($videoId)
            : $this->buildYoutubeUrl($videoId);
        $source->quality = $quality;
        $source->save();
    }

    private function buildOkRuUrl(string $videoId): string
    {
        return 'https://ok.ru/video/' . ltrim($videoId, '/');
    }

    private function buildYoutubeUrl(string $videoId): string
    {
        if (str_contains($videoId, 'http://') || str_contains($videoId, 'https://')) {
            return $videoId;
        }

        return 'https://www.youtube.com/watch?v=' . $videoId;
    }
}
