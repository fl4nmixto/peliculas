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
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:border-sky-300 hover:bg-white/[0.08] hover:text-sky-300"
                        data-share-button
                        data-share-url="{{ url()->current() }}"
                        data-share-title="{{ $person->name }}"
                        data-share-text="Descubre a {{ $person->name }} en el catálogo"
                        data-share-status-id="person-share-status"
                        aria-label="Compartir {{ $person->name }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8l-4 4 4 4M3 12h12a6 6 0 010 12" />
                        </svg>
                        <span>Compartir</span>
                    </button>
                    <span id="person-share-status" class="min-h-[1.25rem] text-[0.7rem] text-slate-400"></span>
                </div>
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
