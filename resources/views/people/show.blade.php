@extends('layouts.app')

@section('title', $person->name)

@section('content')
<section class="flex flex-col gap-5">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-4 rounded-3xl border border-white/10 bg-white/5 p-6 text-slate-200 shadow-2xl md:flex-row md:items-center">
        <div class="flex-shrink-0">
            @if ($person->image_url)
            <img
                src="{{ $person->image_url }}"
                alt="Foto de {{ $person->name }}"
                class="h-32 w-32 rounded-full border border-white/10 object-cover shadow-xl" />
            @else
            <div
                class="flex h-32 w-32 items-center justify-center rounded-full border border-white/10 bg-white/10 text-4xl font-bold uppercase text-white shadow-xl">
                {{ mb_strtoupper(mb_substr($person->name, 0, 1)) }}
            </div>
            @endif
        </div>
        <div class="flex-1 space-y-3">
            <div>
                <h1 class="font-['Space_Grotesk'] text-4xl font-semibold text-white">{{ $person->name }}</h1>
                @php
                    $uniqueMoviesCount = $movies->unique('id')->count();
                @endphp
                <p class="mt-1 text-sm text-slate-300">
                    Participó en {{ $uniqueMoviesCount }}
                    {{ \Illuminate\Support\Str::plural('producción', $uniqueMoviesCount) }} del catálogo.
                </p>
            </div>
            <p class="text-sm text-slate-200">{{ $person->bio ?? '' }}</p>
            @php
                $roleBadges = $movies
                    ->map(fn ($movie) => optional($movie->pivot->role))
                    ->filter()
                    ->unique('id');
            @endphp
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach ($roleBadges as $role)
                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em]">
                    {{ $role->name }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mx-auto w-full max-w-4xl space-y-4">
        @php
            $moviesByRole = $movies
                ->groupBy(fn ($movie) => optional($movie->pivot->role)->name ?? 'Participaciones')
                ->map(fn ($group) => $group->unique('id'));
        @endphp
        @foreach ($moviesByRole as $role => $moviesGroup)
        <section class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200">
            <h2 class="mb-3 font-['Space_Grotesk'] text-lg uppercase tracking-[0.2em] text-white">
                {{ $role }}
            </h2>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($moviesGroup as $movie)
                <article class="flex items-center gap-4 rounded-xl border border-white/5 bg-black/10 p-3">
                    <a
                        href="{{ route('movies.show', $movie) }}"
                        class="block h-16 w-12 flex-shrink-0 overflow-hidden rounded-lg bg-slate-800 shadow-lg"
                        aria-label="Ver {{ $movie->title }}">
                        <img src="{{ $movie->image_url }}" alt="Afiche {{ $movie->title }}" class="h-full w-full object-cover" />
                    </a>
                    <div class="flex-1">
                        <a href="{{ route('movies.show', $movie) }}" class="text-base font-semibold text-white hover:underline">
                            {{ $movie->title }}
                        </a>
                        <p class="text-xs text-slate-400">
                            {{ collect([$movie->year, $movie->genres->pluck('name')->implode(', ')])->filter()->implode(' • ') }}
                        </p>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endforeach
    </div>
</section>
@endsection
