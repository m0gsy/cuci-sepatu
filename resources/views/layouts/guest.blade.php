<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'StepShineWorks') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <div class="mb-6">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="StepShineWorks - Beranda">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <span class="text-white text-sm font-bold">SS</span>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">StepShineWorks</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-sm border border-gray-100 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-gray-400">
                <a href="{{ url('/privacy-policy') }}" class="hover:text-gray-600">Privasi</a>
                &middot;
                <a href="{{ url('/terms') }}" class="hover:text-gray-600">Syarat</a>
                &middot;
                <a href="{{ url('/contact') }}" class="hover:text-gray-600">Kontak</a>
            </p>
        </div>
    </body>
</html>
