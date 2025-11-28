<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>peliculas | @yield('title', 'Catálogo')</title>
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600&display=swap"
            rel="stylesheet"
        />
        @vite(['resources/css/app.css'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_#1c2337_0%,_#05060b_55%)] font-[Inter] text-slate-50">
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col gap-8 px-4 py-6 md:px-6 lg:px-8">
            <header class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                <a
                    href="{{ url('/') }}"
                    class="font-['Space_Grotesk'] text-3xl font-semibold uppercase tracking-[0.4em] text-white transition hover:text-sky-200"
                >
                    peliculas
                </a>
                <nav class="flex flex-wrap gap-4 text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-slate-400">
                </nav>
            </header>

            @yield('content')

            <footer class="mt-auto border-t border-white/10 pt-4 text-sm text-slate-400">
                ¿Qué se puede hacer salvo ver películas?
            </footer>
        </main>
        @stack('scripts')
    </body>
</html>
