<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'BP Control') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="landing-body min-h-screen text-rose-950 antialiased">
        <div class="landing-orb landing-orb-left"></div>
        <div class="landing-orb landing-orb-right"></div>
        <div class="landing-noise"></div>

        <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-6 sm:px-8 lg:px-12">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-700 text-white shadow-lg shadow-rose-300/70">
                    <x-application-logo class="h-7 w-7" />
                </div>
                <div>
                    <p class="text-sm font-semibold tracking-wide text-rose-950">BP Control</p>
                    <p class="text-xs text-rose-700/80">Bakalárske posudky</p>
                </div>
            </a>

            @if (Route::has('login'))
                @auth
                    <a href="{{ route('reviews.index') }}" class="rounded-xl bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-800">Ísť do aplikácie</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-800">Prihlásiť sa</a>
                @endauth
            @endif
        </header>

        <main class="relative z-10 mx-auto w-full max-w-7xl px-5 pb-16 sm:px-8 lg:px-12 lg:pb-24">
            <section class="landing-hero-grid items-center gap-10 lg:grid-cols-2">
                <div class="space-y-7">
                    <p class="inline-flex rounded-full border border-rose-300/80 bg-rose-50/90 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">
                        Moderná študentská aplikácia
                    </p>
                    <h1 class="font-['Sora'] text-4xl font-extrabold leading-tight text-rose-950 sm:text-5xl lg:text-6xl">
                        Posudky pre bakalárky
                        <span class="bg-gradient-to-r from-rose-700 via-red-600 to-rose-500 bg-clip-text text-transparent">rýchlo, čisto a bez chaosu</span>
                    </h1>
                    <p class="max-w-xl text-base leading-relaxed text-rose-900/75 sm:text-lg">
                        BP Control zrýchľuje prácu vedúceho: vyplnenie hodnotenia, filtrovanie záznamov a export finálneho PDF podľa školy.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('reviews.index') }}" class="rounded-xl bg-rose-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-300/70 transition hover:-translate-y-0.5 hover:bg-rose-800">Otvoriť dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-rose-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-300/70 transition hover:-translate-y-0.5 hover:bg-rose-800">Prihlásiť sa</a>
                        @endauth
                        <a href="#ako-to-funguje" class="rounded-xl border border-rose-300 bg-white/90 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:-translate-y-0.5 hover:border-rose-400 hover:bg-white">Ako to funguje</a>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="landing-metric">
                            <p class="landing-metric-value">3 kroky</p>
                            <p class="landing-metric-label">od návrhu po PDF</p>
                        </div>
                        <div class="landing-metric">
                            <p class="landing-metric-value">1 formulár</p>
                            <p class="landing-metric-label">so všetkými kritériami</p>
                        </div>
                        <div class="landing-metric">
                            <p class="landing-metric-value">100%</p>
                            <p class="landing-metric-label">prehľad pre admina</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="surface-card landing-card rounded-3xl p-6 sm:p-7">
                        <p class="text-sm font-semibold uppercase tracking-wide text-rose-700/70">Rýchly náhľad workflowu</p>
                        <div class="mt-5 space-y-3">
                            <div class="landing-step">
                                <span>1</span>
                                <p>Prihlásenie a výber režimu</p>
                            </div>
                            <div class="landing-step">
                                <span>2</span>
                                <p>Vyplnenie posudku s validáciou</p>
                            </div>
                            <div class="landing-step">
                                <span>3</span>
                                <p>Export do finálneho PDF</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="ako-to-funguje" class="mt-14 grid gap-4 sm:mt-16 md:grid-cols-3">
                <article class="surface-card rounded-2xl p-5">
                    <h2 class="text-base font-bold text-rose-950">1. Landing</h2>
                    <p class="mt-2 text-sm leading-relaxed text-rose-900/75">Úvodná stránka vysvetlí projekt a ponúkne jasné CTA.</p>
                </article>
                <article class="surface-card rounded-2xl p-5">
                    <h2 class="text-base font-bold text-rose-950">2. Auth</h2>
                    <p class="mt-2 text-sm leading-relaxed text-rose-900/75">Prihlásenie ide cez /login a po úspechu nasleduje dashboard.</p>
                </article>
                <article class="surface-card rounded-2xl p-5">
                    <h2 class="text-base font-bold text-rose-950">3. App</h2>
                    <p class="mt-2 text-sm leading-relaxed text-rose-900/75">Chránené routy riešia reálnu prácu: posudky, PDF export a admin panel.</p>
                </article>
            </section>
        </main>
    </body>
</html>
