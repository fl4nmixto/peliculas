@extends('layouts.app')

@section('title', 'Catálogo')

@section('content')
    <section class="flex flex-col gap-5" id="catalogo">
        <div class="flex flex-wrap items-baseline justify-between gap-3">
        </div>

        @if ($movies->isNotEmpty())
            <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6">
                @foreach ($movies as $movie)
                    <article class="rounded-2xl border border-white/10 bg-white/5 p-3 text-xs shadow-lg shadow-black/30">
                        <a
                            href="{{ route('movies.show', $movie) }}"
                            class="group block focus:outline-none"
                            aria-label="Ver más sobre {{ $movie->title }}"
                        >
                            <div
                                class="relative mb-3 aspect-[2/3] overflow-hidden rounded-xl bg-slate-800 bg-cover bg-center shadow-xl transition group-hover:scale-[1.02]"
                                style="background-image: url('{{ $movie->image_url }}');"
                            ></div>
                        </a>
                        <h3 class="text-sm font-semibold text-white">{{ $movie->title }}</h3>
                        @php
                            $meta = collect([
                                $movie->genres->pluck('name')->implode(', '),
                                $movie->year,
                                $movie->duration ? $movie->duration . ' min' : null,
                            ])->filter()->implode(' • ');
                        @endphp
                        <p class="text-[0.6rem] text-slate-400">{{ $meta }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400">No existen películas cargadas en el catálogo.</p>
        @endif
    </section>
@endsection
