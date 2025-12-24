@extends('layouts.app')

@section('title', $movie->title)

@section('content')
<section class="flex flex-col gap-5">
    <div
        class="mx-auto grid w-full grid-cols-1 gap-6 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur-sm md:grid-cols-2 lg:grid-cols-[minmax(200px,240px)_1fr]">
        <div class="flex flex-col items-center gap-4 md:items-start">
            <img
                class="max-h-[300px] w-auto rounded-2xl border border-white/15 bg-slate-900/40 p-2 shadow-xl"
                src="{{ $movie->image_url }}"
                alt="Afiche de {{ $movie->title }}" />
            <div class="flex w-full flex-col gap-3 text-center text-sm text-slate-200 md:text-left">
                <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-white">{{ $movie->title }}</h1>
                @php
                    $countries = collect($movie->countries ?? [])->filter()->implode(', ');
                    $languages = collect($movie->spoken_languages ?? [])->filter()->implode(', ');
                    $meta = collect([
                        $movie->year
                            ? '<a href="' . route('years.show', $movie->year) . '" class="text-sky-300 hover:underline">' . e($movie->year) . '</a>'
                            : null,
                        $movie->duration ? $movie->duration . ' min' : null,
                    ])->filter()->implode(' • ');
                @endphp
                @if ($meta)
                <p class="text-slate-400">{!! $meta !!}</p>
                @endif
                <div class="flex flex-wrap items-center justify-center gap-3 md:justify-start">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:border-sky-300 hover:bg-white/[0.08] hover:text-sky-300"
                        data-share-button
                        data-share-url="{{ url()->current() }}"
                        data-share-title="{{ $movie->title }}"
                        data-share-text="Mira {{ $movie->title }} en el catálogo"
                        data-share-status-id="movie-share-status"
                        aria-label="Compartir {{ $movie->title }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8l-4 4 4 4M3 12h12a6 6 0 010 12" />
                        </svg>
                        <span>Compartir</span>
                    </button>
                    <span id="movie-share-status" class="min-h-[1.25rem] text-[0.7rem] text-slate-400"></span>
                </div>
                @if ($movie->original_title && strcasecmp($movie->original_title, $movie->title) !== 0)
                <p class="text-sm text-slate-300">
                    <span class="text-slate-400">Título original:</span>
                    {{ $movie->original_title }}
                </p>
                @endif
                @if ($countries || $languages)
                <dl class="mt-2 space-y-1 text-xs uppercase tracking-[0.3em] text-slate-400">
                    @if ($countries)
                    <div>
                        <dt>Países</dt>
                        <dd class="text-sm font-medium normal-case tracking-normal text-white/90">
                            {{ $countries }}
                        </dd>
                    </div>
                    @endif
                    @if ($languages)
                    <div>
                        <dt>Idiomas</dt>
                        <dd class="text-sm font-medium normal-case tracking-normal text-white/90">
                            {{ $languages }}
                        </dd>
                    </div>
                    @endif
                </dl>
                @endif
                @if ($movie->trailer_embed_url)
                <div class="mt-4 w-full overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-inner">
                    <div class="relative w-full" style="padding-bottom: 56.25%;">
                        <iframe
                            src="{{ $movie->trailer_embed_url }}"
                            title="Tráiler de {{ $movie->title }}"
                            class="absolute left-0 top-0 h-full w-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @php
            $topCredits = [
                'Dirección' => $credits['directors'],
                'Protagonistas' => $credits['featuredCast'],
                'Elenco' => $credits['cast'],
            ];
        @endphp
        <div class="flex flex-col gap-4 text-sm text-slate-200">
            @php
                $videoEmbedUrl = $movie->video_embed_url;
            @endphp
            @if ($videoEmbedUrl)
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-inner">
                <div class="relative w-full" style="padding-bottom: 56.25%;">
                    <iframe
                        src="{{ $videoEmbedUrl }}"
                        title="Reproductor de {{ $movie->title }}"
                        class="absolute left-0 top-0 h-full w-full"
                        allow="autoplay; fullscreen"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
            @endif
            @if ($movie->synopsis)
            <div>
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-slate-400">Sinopsis</h3>
                <p class="mt-1 text-slate-200/90">
                    {{ $movie->synopsis }}
                </p>
            </div>
            @endif
            <div>
                <p class="text-slate-400">Géneros:</p>
                <p class="mt-1">
                    @if ($movie->genres->isNotEmpty())
                        {!! $movie->genres->map(function ($genre) {
                                return '<a href="' . route('genres.show', $genre) . '" class="text-sky-300 hover:underline">' . e($genre->name) . '</a>';
                            })->implode(', ') !!}
                    @else
                        Sin género asignado
                    @endif
                </p>
            </div>
            @foreach ($topCredits as $label => $people)
                @if ($people->isNotEmpty())
                <div>
                    <p class="text-slate-400">{{ $label }}:</p>
                    <ul class="mt-3 flex flex-wrap gap-3">
                        @foreach ($people as $person)
                        <li class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-white">
                            @if ($person->image_url)
                            <img
                                src="{{ $person->image_url }}"
                                alt="Foto de {{ $person->name }}"
                                class="h-10 w-10 rounded-full border border-white/10 object-cover" />
                            @else
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/10 text-xs font-bold uppercase text-white">
                                {{ mb_strtoupper(mb_substr($person->name, 0, 1)) }}
                            </div>
                            @endif
                            @if ($person->slug)
                            <a href="{{ route('people.show', $person) }}" class="hover:text-sky-300">
                                {{ $person->name }}
                            </a>
                            @else
                            <span>{{ $person->name }}</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            @endforeach
            @if ($movie->sources->isNotEmpty())
            <div>
                <p class="text-slate-400">Disponible en:</p>
                <ul class="mt-1 space-y-1 text-sm">
                    @foreach ($movie->sources as $source)
                    <li class="font-semibold text-sky-300">
                        <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer" class="hover:underline">
                            {{ $source->provider->name ?? 'Fuente' }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div class="mx-auto w-full">
        @php
            $crewByRole = $credits['crew']
                ->groupBy(fn ($person) => optional($person->pivot->role)->name)
                ->sortBy(function ($people) {
                    return optional($people->first()->pivot->role)->position ?? 999;
                });
        @endphp

        @if ($crewByRole->isNotEmpty())
        <section class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200">
            <h2 class="mb-2 font-['Space_Grotesk'] text-lg uppercase tracking-[0.2em] text-white">Equipo</h2>
            <div class="space-y-3">
                @foreach ($crewByRole as $roleName => $people)
                <div>
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-slate-400">
                        {{ $roleName ?? 'Equipo' }}
                    </h3>
                                <ul class="mt-1 space-y-1 font-medium text-white">
                                    @foreach ($people as $person)
                                        <li>
                                            @if ($person->slug)
                                                <a
                                                    href="{{ route('people.show', $person) }}"
                                                    class="text-white transition hover:text-sky-300"
                                                >
                                                    {{ $person->name }}
                                                </a>
                                            @else
                                                <span>{{ $person->name }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>

</section>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const shareButtons = document.querySelectorAll('[data-share-button]');
        shareButtons.forEach((button) => {
            const statusId = button.dataset.shareStatusId;
            const statusEl = statusId ? document.getElementById(statusId) : null;
            let statusTimeout;

            const setStatus = (message, isError = false) => {
                if (!statusEl) {
                    return;
                }
                statusEl.textContent = message;
                statusEl.classList.toggle('text-rose-300', isError);
                statusEl.classList.toggle('text-slate-400', !isError);
                clearTimeout(statusTimeout);
                statusTimeout = setTimeout(() => {
                    statusEl.textContent = '';
                }, 2500);
            };

            button.addEventListener('click', async () => {
                const payload = {
                    title: button.dataset.shareTitle || '',
                    text: button.dataset.shareText || '',
                    url: button.dataset.shareUrl || window.location.href,
                };

                try {
                    if (navigator.share) {
                        await navigator.share(payload);
                        setStatus('Compartido');
                        return;
                    }

                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(payload.url);
                        setStatus('Enlace copiado');
                        return;
                    }

                    setStatus('No se pudo compartir', true);
                } catch (error) {
                    setStatus('No se pudo compartir', true);
                }
            });
        });
    });
</script>
@endpush
@endsection
