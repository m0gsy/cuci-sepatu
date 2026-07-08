<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Step Shine Works') }} — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .badge-antri        { @apply bg-amber-50 text-amber-800 ring-1 ring-amber-200; }
        .badge-proses       { @apply bg-blue-50 text-blue-800 ring-1 ring-blue-200; }
        .badge-selesai      { @apply bg-green-50 text-green-800 ring-1 ring-green-200; }
        .badge-diambil      { @apply bg-gray-100 text-gray-600 ring-1 ring-gray-200; }
        .badge-diterima     { @apply bg-slate-100 text-slate-700 ring-1 ring-slate-200; }
        .badge-inspeksi     { @apply bg-purple-50 text-purple-700 ring-1 ring-purple-200; }
        .badge-dicuci       { @apply bg-blue-50 text-blue-700 ring-1 ring-blue-200; }
        .badge-kering       { @apply bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200; }
        .badge-finishing    { @apply bg-orange-50 text-orange-700 ring-1 ring-orange-200; }
        .badge-siap_diambil { @apply bg-green-50 text-green-700 ring-1 ring-green-200; }
        .nav-active         { background: rgba(255,255,255,0.12); color: white; font-weight: 500; }
        .nav-link           { color: rgba(187,230,207,0.65); }
        .nav-link:hover     { background: rgba(255,255,255,0.08); color: white; }
        .nav-footer-link    { color: rgba(187,230,207,0.55); }
        .nav-footer-link:hover { color: white; }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
<div class="flex h-full" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">
    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 z-20 lg:hidden" x-cloak></div>
    <aside class="w-52 flex flex-col shrink-0 fixed inset-y-0 left-0 z-30 overflow-y-auto transition-transform duration-200"
           style="background:#1a3a2a;"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="px-5 py-5 shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.08);">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                    <span class="text-white text-xs font-bold">SS</span>
                </div>
                <span class="font-semibold text-sm text-white">Step Shine Works</span>
            </div>
            <div class="pl-9 flex items-center gap-1.5">
                <p class="text-xs" style="color:rgba(187,230,207,0.7);">{{ auth()->user()->name }}</p>
                @if(method_exists(auth()->user(), 'isAdmin'))
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" style="background:rgba(255,255,255,0.1);color:rgba(187,230,207,0.9);">
                    {{ auth()->user()->role_label ?? 'Staf' }}
                </span>
                @endif
            </div>
        </div>
        <nav class="flex-1 py-3 px-2">
            @php
                $user = auth()->user();
                $isOwner = $user?->isOwner();
                $isAdmin = $user && !$user->isOwner() && !$user->isCleaner(); // strict admin
                $isCleaner = $user?->isCleaner();

                // Build nav based on role
                $navItems = [['route' => 'dashboard', 'label' => 'Dashboard', 'match' => 'dashboard']];

                if ($isOwner) {
                    $navItems = array_merge($navItems, [
                        ['route' => 'orders.index',     'label' => 'Daftar order',   'match' => 'orders.*'],
                        ['route' => 'pelanggans.index', 'label' => 'Pelanggan',      'match' => 'pelanggans.*'],
                        ['route' => 'hpp.laporan',      'label' => 'Profit / Loss',  'match' => 'hpp.laporan'],
                        ['route' => 'laporan',          'label' => 'Laporan',        'match' => 'laporan*'],
                        ['route' => 'operasional.index','label' => 'Operasional',    'match' => 'operasional.*'],
                        ['route' => 'vouchers.index',   'label' => 'Voucher',        'match' => 'vouchers.*'],
                        ['route' => 'reviews.index',    'label' => 'Ulasan',         'match' => 'reviews.*'],
                        ['route' => 'rewards.index',    'label' => 'Reward & Poin',  'match' => 'rewards.*'],
                        ['route' => 'wa-templates.index','label' => 'Template WA',   'match' => 'wa-templates.*'],
                    ]);
                } else {
                    // Admin/Cleaner: tampilkan menu sesuai permission
                    $permMap = [
                        'orders.manage' => ['route' => 'orders.create',    'label' => 'Order baru',     'match' => 'orders.create'],
                        'orders.index'  => ['route' => 'orders.index',     'label' => 'Daftar order',   'match' => 'orders.index'],
                        'pelanggan'     => ['route' => 'pelanggans.index', 'label' => 'Pelanggan',      'match' => 'pelanggans.*'],
                        'lokasi'        => ['route' => 'lokasi.index',     'label' => 'Lokasi sepatu',  'match' => 'lokasi.*'],
                        'laporan'       => ['route' => 'laporan',          'label' => 'Laporan',        'match' => 'laporan*'],
                        'hpp'           => ['route' => 'hpp.laporan',      'label' => 'Profit / Loss',  'match' => 'hpp.laporan'],
                        'vouchers'      => ['route' => 'vouchers.index',   'label' => 'Voucher',        'match' => 'vouchers.*'],
                        'rewards'       => ['route' => 'rewards.index',    'label' => 'Reward & Poin',  'match' => 'rewards.*'],
                        'operasional'   => ['route' => 'operasional.index','label' => 'Operasional',    'match' => 'operasional.*'],
                        'wa_template'   => ['route' => 'wa-templates.index','label' => 'Template WA',  'match' => 'wa-templates.*'],
                    ];

                    // Daftar order selalu ada untuk semua
                    $navItems[] = ['route' => 'orders.index', 'label' => $isCleaner ? 'Pekerjaan saya' : 'Daftar order', 'match' => 'orders.index'];

                    foreach ($permMap as $perm => $item) {
                        if ($perm === 'orders.index') continue;
                        if ($user->hasPermission($perm)) {
                            $navItems[] = $item;
                        }
                    }

                    // Ulasan selalu muncul untuk admin (bukan cleaner) — customer yang input
                    if ($isAdmin && !$isOwner) {
                        $navItems[] = ['route' => 'reviews.index', 'label' => 'Ulasan', 'match' => 'reviews.*'];
                    }
                }
            @endphp
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-sm mb-0.5 transition-colors {{ $active ? 'nav-active' : 'nav-link' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            @php
                $canLayanan = $isOwner || ($user && $user->hasPermission('layanans'));
                $canStok = $isOwner || ($user && $user->hasPermission('stok'));
                $canHpp = $isOwner || ($user && $user->hasPermission('hpp'));
                $showKelola = $canLayanan || $canStok || $canHpp;
            @endphp

            @if($showKelola)
            <div x-data="{ open: {{ (
                request()->routeIs('layanans.*') ||
                request()->routeIs('kategori-layanans.*') ||
                request()->routeIs('jenis-barangs.*') ||
                request()->routeIs('bahans.*') ||
                request()->routeIs('hpp.index') ||
                request()->routeIs('stok.*')
            ) ? 'true' : 'false' }} }" class="mt-2.5">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm mb-0.5 transition-colors nav-link hover:text-white focus:outline-none">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Kelola</span>
                    </span>
                    <svg class="w-3.5 h-3.5 transform transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-0.5 mt-0.5">
                    @if($canLayanan)
                    <a href="{{ route('layanans.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('layanans.*') ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Master Pelayanan
                    </a>
                    <a href="{{ route('kategori-layanans.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('kategori-layanans.*') ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Kategori Layanan
                    </a>
                    <a href="{{ route('jenis-barangs.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('jenis-barangs.*') ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Jenis Barang
                    </a>
                    @endif

                    @if($canStok)
                    <a href="{{ route('bahans.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('bahans.*') ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Daftar Bahan Baku
                    </a>
                    @endif

                    @if($isOwner)
                    <a href="{{ route('hpp.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('hpp.index') ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Kelola Bahan Baku
                    </a>
                    @endif

                    @if($canStok)
                    <a href="{{ route('stok.index') }}"
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ (request()->routeIs('stok.*') && !request()->routeIs('bahans.*')) ? 'nav-active' : 'nav-link hover:text-white' }}">
                        Stok Bahan Baku
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if($isOwner)
            <div class="mt-3 mb-1 px-3">
                <p class="text-xs font-medium uppercase tracking-wider" style="color:rgba(187,230,207,0.4);">Sistem</p>
            </div>
            <a href="{{ route('lokasi.index') }}"
               class="flex items-center px-3 py-2 rounded-lg text-sm mb-0.5 transition-colors {{ request()->routeIs('lokasi.*') ? 'nav-active' : 'nav-link' }}">
                Lokasi sepatu
            </a>
            <a href="{{ route('karyawans.index') }}"
               class="flex items-center px-3 py-2 rounded-lg text-sm mb-0.5 transition-colors {{ request()->routeIs('karyawans.*') ? 'nav-active' : 'nav-link' }}">
                Karyawan
            </a>
            @endif
        </nav>
        <div class="px-5 py-4 shrink-0" style="border-top:1px solid rgba(255,255,255,0.08);">
            <p class="text-xs mb-1" style="color:rgba(187,230,207,0.5);">{{ now()->isoFormat('D MMM Y') }}</p>
            <a href="{{ route('profil.index') }}" class="block text-xs mb-1.5 transition-colors nav-footer-link">Profil &amp; password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs transition-colors nav-footer-link">Keluar →</button>
            </form>
        </div>
    </aside>
    <div class="flex-1 flex flex-col min-h-full transition-all duration-200"
         :class="sidebarOpen ? 'lg:ml-52' : 'ml-0'">
        <header class="bg-white border-b border-gray-100 px-4 py-3.5 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        aria-label="Toggle sidebar"
                        class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-sm font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">@yield('header-actions')</div>
        </header>
        @if(session('success'))
        <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-900 ml-4">✕</button>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-900 ml-4">✕</button>
        </div>
        @endif
        @if(session('warning'))
        <div class="mx-6 mt-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg flex items-center justify-between" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
            <span>{{ session('warning') }}</span>
            <button @click="show = false" class="text-amber-600 hover:text-amber-900 ml-4">✕</button>
        </div>
        @endif
        <main class="flex-1 p-6">@yield('content')</main>
    </div>
</div>
</body>
</html>
