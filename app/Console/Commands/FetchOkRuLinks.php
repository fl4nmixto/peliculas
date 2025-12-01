<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchOkRuLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:fetch-okru-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find the first ok.ru video link for every stored movie via Google Search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $movies = Movie::query()->orderBy('title')->get();

        if ($movies->isEmpty()) {
            $this->warn('No movies found.');

            return self::SUCCESS;
        }

        foreach ($movies as $movie) {
            $query = sprintf('%s site:https://ok.ru/video/', $movie->title);
            $this->line(PHP_EOL . "🔎 {$movie->title}");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; CodexBot/1.0; +https://localhost)',
                ])->get('https://www.google.com/search', [
                    'q' => $query,
                    'num' => 5,
                    'hl' => 'es',
                ]);
            } catch (\Throwable $exception) {
                $this->error("Failed request: {$exception->getMessage()}");
                continue;
            }

            if (! $response->successful()) {
                $this->error('Google returned HTTP ' . $response->status());
                continue;
            }

            $link = $this->extractFirstOkRuLink($response->body());

            if ($link) {
                $this->info("➡  {$link}");
            } else {
                $this->warn('No ok.ru link found in the results.');
            }

            usleep(500000); // small pause to avoid being rate-limited
        }

        return self::SUCCESS;
    }

    /**
     * Extracts the first ok.ru link from a Google Search HTML page.
     */
    protected function extractFirstOkRuLink(string $html): ?string
    {
        if (! preg_match_all('/\/url\?q=(https?:\/\/[^&]+)&/i', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $encodedUrl) {
            $url = urldecode($encodedUrl);
            $host = parse_url($url, PHP_URL_HOST) ?? '';

            if (Str::contains($host, 'ok.ru')) {
                return $url;
            }
        }

        return null;
    }
}
