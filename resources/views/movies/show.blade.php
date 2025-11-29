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
                @if ($movie->tagline)
                <p class="text-base font-medium text-white/80">{{ $movie->tagline }}</p>
                @endif
                @php
                $meta = collect([
                $movie->year,
                $movie->duration ? $movie->duration . ' min' : null,
                ])->filter()->implode(' • ');
                @endphp
                @if ($meta)
                <p class="text-slate-400">{{ $meta }}</p>
                @endif
                <div class="flex items-center justify-center gap-3 md:justify-start">
                    <div class="flex gap-1.5">
                        @for ($i = 1; $i <= 5; $i++)
                            <span
                            class="h-2.5 w-2.5 rounded-full {{ $i <= $movie->score ? 'bg-emerald-300' : 'bg-white/20' }}"></span>
                            @endfor
                    </div>
                    <span class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ $movie->score }}/5</span>
                </div>
                <p><span class="text-slate-400">Clasificación:</span> {{ $movie->rating ?? 'Sin información' }}</p>
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
                    {{ $movie->genres->isNotEmpty() ? $movie->genres->pluck('name')->implode(', ') : 'Sin género asignado' }}
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
                            <a href="{{ route('people.show', $person) }}" class="hover:text-sky-300">
                                {{ $person->name }}
                            </a>
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
            @if ($movie->trailer_embed_url)
            <div class="mb-4 overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-inner">
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
            <div class="space-y-3">
                @foreach ($crewByRole as $roleName => $people)
                <div>
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-slate-400">
                        {{ $roleName ?? 'Equipo' }}
                    </h3>
                                <ul class="mt-1 space-y-1 font-medium text-white">
                                    @foreach ($people as $person)
                                        <li>
                                            <a
                                                href="{{ route('people.show', $person) }}"
                                                class="text-white transition hover:text-sky-300"
                                            >
                                                {{ $person->name }}
                                            </a>
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
@endsection
