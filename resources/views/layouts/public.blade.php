<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary SEO -->
    <title>@yield('meta_title', 'StepShineWorks — Platform Manajemen Jasa Cuci Sepatu')</title>
    <meta name="description" content="@yield('meta_description', 'StepShineWorks adalah platform manajemen jasa cuci sepatu modern. Kelola order, tracking real-time, notifikasi WhatsApp otomatis, dan laporan bisnis.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="StepShineWorks">
    <meta property="og:title" content="@yield('og_title', 'StepShineWorks — Platform Manajemen Jasa Cuci Sepatu')">
    <meta property="og:description" content="@yield('og_description', 'Platform manajemen jasa cuci sepatu modern dengan tracking real-time dan notifikasi WhatsApp.')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', url('/og-image.png'))">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', 'StepShineWorks')">
    <meta name="twitter:description" content="@yield('meta_description', 'Platform manajemen jasa cuci sepatu modern.')">
    <meta name="twitter:image" content="@yield('og_image', url('/og-image.png'))">

    <!-- Favicon & Manifest -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Schema.org JSON-LD -->
    @yield('schema')

    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-white text-gray-900 antialiased">

<!-- Navigation -->
<nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm" x-data="{ open: false }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0" aria-label="StepShineWorks - Beranda">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                    <span class="text-white text-xs font-bold" aria-hidden="true">SS</span>
                </div>
                <span class="font-bold text-gray-900">StepShineWorks</span>
            </a>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-0.5" role="navigation" aria-label="Menu utama">
                <a href="{{ url('/') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50 transition-colors">Beranda</a>
                <a href="{{ url('/#fitur') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50 transition-colors">Fitur</a>
                <a href="{{ url('/about') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50 transition-colors">Tentang</a>
                <a href="{{ url('/contact') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50 transition-colors">Kontak</a>
            </div>

            <!-- Auth Links -->
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900 font-medium transition-colors">Masuk</a>
                @if(Route::has('register'))
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">Mulai Gratis</a>
                @endif
            </div>

            <!-- Mobile Hamburger -->
            <button @click="open = !open"
                    class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                    aria-label="Buka menu navigasi"
                    :aria-expanded="open">
                <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-cloak class="md:hidden border-t border-gray-100 bg-white pb-4" role="navigation" aria-label="Menu mobile">
        <div class="px-4 pt-3 space-y-1">
            <a href="{{ url('/') }}" class="block px-3 py-2.5 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">Beranda</a>
            <a href="{{ url('/#fitur') }}" class="block px-3 py-2.5 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">Fitur</a>
            <a href="{{ url('/about') }}" class="block px-3 py-2.5 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">Tentang</a>
            <a href="{{ url('/contact') }}" class="block px-3 py-2.5 text-sm text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-50">Kontak</a>
        </div>
        <div class="px-4 pt-3 border-t border-gray-100 mt-2 flex gap-3">
            <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 font-medium">Masuk</a>
            @if(Route::has('register'))
            <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Mulai Gratis</a>
            @endif
        </div>
    </div>
</nav>

<!-- Main Content -->
<main id="main-content" tabindex="-1">
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-400" role="contentinfo" aria-label="Footer">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

            <!-- Brand -->
            <div class="sm:col-span-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 mb-4" aria-label="StepShineWorks">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <span class="text-white text-xs font-bold" aria-hidden="true">SS</span>
                    </div>
                    <span class="font-bold text-white">StepShineWorks</span>
                </a>
                <p class="text-sm text-gray-400 leading-relaxed max-w-xs">
                    Platform manajemen jasa cuci sepatu modern. Kelola order, tracking real-time, dan notifikasi WhatsApp otomatis untuk bisnis Anda.
                </p>
                <div class="mt-5 space-y-1.5">
                    <a href="mailto:support@stepshineworks.store" class="block text-sm text-gray-400 hover:text-white transition-colors">
                        support@stepshineworks.store
                    </a>
                    <p class="text-sm text-gray-500">Indonesia</p>
                </div>
            </div>

            <!-- Company Links -->
            <nav aria-label="Tautan perusahaan">
                <h3 class="text-xs font-semibold text-gray-200 uppercase tracking-wider mb-4">Perusahaan</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ url('/about') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Tentang Kami</a></li>
                    <li><a href="{{ url('/contact') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Kontak</a></li>
                    <li><a href="{{ url('/#fitur') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Fitur</a></li>
                    <li><a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Masuk</a></li>
                </ul>
            </nav>

            <!-- Legal Links -->
            <nav aria-label="Tautan legal">
                <h3 class="text-xs font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ url('/privacy-policy') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="{{ url('/terms') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Syarat &amp; Ketentuan</a></li>
                    <li><a href="{{ url('/refund-policy') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Kebijakan Pengembalian</a></li>
                </ul>
            </nav>
        </div>

        <div class="mt-10 pt-8 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} StepShineWorks. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <a href="{{ url('/privacy-policy') }}" class="hover:text-gray-300 transition-colors">Privasi</a>
                <span aria-hidden="true">&middot;</span>
                <a href="{{ url('/terms') }}" class="hover:text-gray-300 transition-colors">Syarat</a>
                <span aria-hidden="true">&middot;</span>
                <a href="{{ url('/contact') }}" class="hover:text-gray-300 transition-colors">Kontak</a>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
