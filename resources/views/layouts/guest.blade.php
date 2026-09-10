<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body class="app-shell font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 pt-8 sm:pt-0">
            <div>
                <a href="/">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-700 text-white shadow-lg shadow-rose-300/70">
                        <x-application-logo class="h-12 w-12" />
                    </div>
                </a>
                <p class="mt-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600">BP Control</p>
            </div>

            <div class="surface-card w-full sm:max-w-md mt-6 px-6 py-5 overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
