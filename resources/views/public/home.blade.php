@extends('layouts.public')

@section('meta_title', 'StepShineWorks — Platform Manajemen Jasa Cuci Sepatu')
@section('meta_description', 'Kelola bisnis cuci sepatu Anda dengan mudah. Order management, tracking real-time 7 tahap, notifikasi WhatsApp otomatis, laporan profit/loss, dan loyalitas pelanggan.')
@section('canonical', url('/'))
@section('og_title', 'StepShineWorks — Platform Manajemen Jasa Cuci Sepatu')
@section('og_description', 'Sistem manajemen jasa cuci sepatu terlengkap. Tracking real-time, WA otomatis, laporan bisnis.')
@section('og_url', url('/'))

@section('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://stepshineworks.store/#organization",
      "name": "StepShineWorks",
      "url": "https://stepshineworks.store",
      "email": "support@stepshineworks.store",
      "description": "Platform manajemen jasa cuci sepatu modern untuk pelaku usaha shoe care di Indonesia.",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "ID"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "email": "support@stepshineworks.store",
        "availableLanguage": "Indonesian"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://stepshineworks.store/#website",
      "url": "https://stepshineworks.store",
      "name": "StepShineWorks",
      "description": "Platform manajemen jasa cuci sepatu modern",
      "publisher": { "@id": "https://stepshineworks.store/#organization" },
      "inLanguage": "id"
    },
    {
      "@type": "SoftwareApplication",
      "name": "StepShineWorks",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "Platform manajemen jasa cuci sepatu modern dengan fitur order management, tracking real-time, dan notifikasi WhatsApp.",
      "url": "https://stepshineworks.store",
      "offers": {
        "@type": "Offer",
        "priceCurrency": "IDR"
      }
    }
  ]
}
</script>
@endverbatim
@endsection

@section('content')

<!-- Hero -->
<section class="bg-gradient-to-br from-blue-700 via-blue-600 to-blue-800 text-white" aria-labelledby="hero-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-1.5 mb-8 text-sm font-medium text-blue-100">
                <span class="w-2 h-2 bg-green-400 rounded-full shrink-0" aria-hidden="true"></span>
                Platform aktif untuk bisnis cuci sepatu Indonesia
            </div>
            <h1 id="hero-heading" class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6" style="text-wrap: balance">
                Manajemen Jasa Cuci Sepatu, Lebih Mudah &amp; Profesional
            </h1>
            <p class="text-xl text-blue-100 leading-relaxed mb-10 max-w-2xl" style="text-wrap: pretty">
                Kelola order, pantau status real-time 7 tahap, kirim notifikasi WhatsApp otomatis ke pelanggan, dan analisis bisnis Anda — semua dalam satu sistem.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors text-base">
                    Mulai Gratis Sekarang
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="#fitur"
                   class="inline-flex items-center justify-center px-7 py-3.5 border border-white/30 text-white font-medium rounded-xl hover:bg-white/10 transition-colors text-base">
                    Lihat Fitur
                </a>
            </div>

            <!-- Quick stats -->
            <div class="mt-14 grid grid-cols-3 gap-6 border-t border-white/20 pt-10">
                <div>
                    <p class="text-3xl font-bold text-white">7</p>
                    <p class="text-sm text-blue-200 mt-1">Tahap tracking order</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white">100%</p>
                    <p class="text-sm text-blue-200 mt-1">Notifikasi WA otomatis</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white">Real-time</p>
                    <p class="text-sm text-blue-200 mt-1">Laporan &amp; analitik</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="fitur" class="py-20 bg-white" aria-labelledby="fitur-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 id="fitur-heading" class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Semua yang Anda Butuhkan</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto" style="text-wrap: pretty">
                Didesain khusus untuk pelaku usaha shoe care. Dari order masuk hingga pelanggan puas — semuanya terkelola rapi.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $features = [
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                    'title' => 'Manajemen Order',
                    'desc' => 'Catat order dengan detail lengkap: jenis sepatu, layanan, harga, voucher, dan estimasi selesai. Semua terorganisir rapi.',
                    'color' => 'bg-blue-50 text-blue-600',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                    'title' => 'Tracking Real-Time 7 Tahap',
                    'desc' => 'Pantau setiap order dari "Diterima" hingga "Selesai" melalui 7 tahap status yang jelas dan terstruktur.',
                    'color' => 'bg-green-50 text-green-600',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                    'title' => 'Notifikasi WhatsApp Otomatis',
                    'desc' => 'Kirim pesan WA otomatis ke pelanggan saat order masuk, mulai dicuci, selesai, dan invoice — tanpa perlu kirim manual.',
                    'color' => 'bg-emerald-50 text-emerald-600',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'title' => 'Laporan &amp; Analitik',
                    'desc' => 'Laporan pendapatan harian/bulanan, HPP per layanan, profit/loss, dan operasional bisnis secara real-time.',
                    'color' => 'bg-purple-50 text-purple-600',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>',
                    'title' => 'Poin Loyalty Pelanggan',
                    'desc' => 'Program reward untuk pelanggan setia. Berikan poin di setiap transaksi dan reward menarik yang bisa ditukar.',
                    'color' => 'bg-amber-50 text-amber-600',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                    'title' => 'Stok &amp; Operasional',
                    'desc' => 'Pantau stok bahan cuci, catat mutasi stok, kelola biaya operasional, dan hindari kehabisan bahan saat sibuk.',
                    'color' => 'bg-rose-50 text-rose-600',
                ],
            ];
            @endphp

            @foreach($features as $f)
            <article class="bg-white border border-gray-100 rounded-2xl p-6 hover:border-blue-100 hover:shadow-md transition-all">
                <div class="w-11 h-11 {{ $f['color'] }} rounded-xl flex items-center justify-center mb-4" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        {!! $f['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">{!! $f['title'] !!}</h3>
                <p class="text-sm text-gray-600 leading-relaxed">{!! $f['desc'] !!}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

<!-- How it Works -->
<section class="py-20 bg-gray-50" aria-labelledby="cara-kerja-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 id="cara-kerja-heading" class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Cara Kerja StepShineWorks</h2>
            <p class="text-lg text-gray-600 max-w-xl mx-auto">Mulai kelola bisnis cuci sepatu Anda dalam 3 langkah sederhana.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
            $steps = [
                ['num' => '1', 'title' => 'Terima Order', 'desc' => 'Catat pesanan pelanggan: nama, kontak, jenis sepatu, layanan, dan metode pembayaran. Nomor order otomatis dibuat.'],
                ['num' => '2', 'title' => 'Proses &amp; Update Status', 'desc' => 'Update status order di setiap tahap — dari diterima, dicuci, kering, finishing, hingga siap diambil. Notifikasi WA terkirim otomatis.'],
                ['num' => '3', 'title' => 'Kirim Invoice &amp; Laporan', 'desc' => 'Kirim invoice via WA, cetak nota, dan pantau laporan bisnis harian, mingguan, atau bulanan secara real-time.'],
            ];
            @endphp

            @foreach($steps as $step)
            <article class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
                <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-5" aria-label="Langkah {{ $step['num'] }}">
                    {{ $step['num'] }}
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">{!! $step['title'] !!}</h3>
                <p class="text-sm text-gray-600 leading-relaxed">{!! $step['desc'] !!}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 bg-white" aria-labelledby="testimoni-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 id="testimoni-heading" class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Dipercaya Pelaku Usaha Sepatu</h2>
            <p class="text-lg text-gray-600">Bergabung bersama pemilik usaha cuci sepatu yang telah merasakan manfaatnya.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
            $testimonials = [
                ['name' => 'Budi Santoso', 'role' => 'Pemilik, BudiSneaker Care — Yogyakarta', 'text' => 'StepShineWorks benar-benar mengubah cara saya mengelola bisnis. Dulu sering kelupaan update status, sekarang WA otomatis terkirim ke pelanggan. Komplain turun drastis!'],
                ['name' => 'Andi Pratama', 'role' => 'Owner, AP Shoe Cleaner — Surabaya', 'text' => 'Fitur tracking 7 tahap sangat membantu. Pelanggan bisa pantau sendiri via link tanpa harus WA terus. Operasional jadi jauh lebih efisien.'],
                ['name' => 'Rina Kurniawati', 'role' => 'Pengelola, Rina Shoe Care — Bandung', 'text' => 'Laporan profit/loss dan HPP per layanan sangat berguna untuk evaluasi bisnis bulanan. Akhirnya tahu layanan mana yang paling untung.'],
            ];
            @endphp

            @foreach($testimonials as $t)
            <blockquote class="bg-gray-50 rounded-2xl p-7 border border-gray-100">
                <div class="flex gap-1 mb-4" aria-label="Rating 5 bintang">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-gray-700 text-sm leading-relaxed mb-5 italic">"{{ $t['text'] }}"</p>
                <footer>
                    <p class="font-semibold text-gray-900 text-sm">{{ $t['name'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $t['role'] }}</p>
                </footer>
            </blockquote>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-20 bg-gray-50" aria-labelledby="faq-heading">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 id="faq-heading" class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Pertanyaan Umum</h2>
            <p class="text-lg text-gray-600">Ada yang ingin Anda tanyakan? Berikut jawaban untuk pertanyaan yang paling sering kami terima.</p>
        </div>

        @php
        $faqs = [
            ['q' => 'Apa itu StepShineWorks?', 'a' => 'StepShineWorks adalah platform manajemen berbasis web khusus untuk bisnis jasa cuci sepatu. Membantu Anda mengelola order, tracking status, notifikasi pelanggan via WhatsApp, dan analitik bisnis dalam satu sistem terintegrasi.'],
            ['q' => 'Apakah bisa digunakan untuk multi-outlet?', 'a' => 'Ya. StepShineWorks mendukung pengelolaan multi-lokasi (outlet) dalam satu akun. Anda bisa memantau semua outlet dari satu dashboard.'],
            ['q' => 'Bagaimana notifikasi WhatsApp bekerja?', 'a' => 'Setiap kali status order berubah (masuk, mulai dicuci, selesai, dll), sistem secara otomatis mengirim pesan WhatsApp ke nomor pelanggan. Anda bisa kustomisasi template pesan sesuai kebutuhan bisnis.'],
            ['q' => 'Apakah data bisnis saya aman?', 'a' => 'Ya. Semua data dienkripsi dan disimpan di server yang aman. Akses dibatasi berdasarkan peran (owner, admin, karyawan) dengan sistem permission yang ketat.'],
            ['q' => 'Bagaimana cara mendaftar atau memulai?', 'a' => 'Klik tombol "Mulai Gratis Sekarang" di halaman ini, atau hubungi kami di support@stepshineworks.store untuk mendapatkan panduan setup awal bisnis Anda.'],
        ];
        @endphp

        <div class="space-y-3" x-data="{ open: null }">
            @foreach($faqs as $idx => $faq)
            <article class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <button
                    @click="open === {{ $idx }} ? open = null : open = {{ $idx }}"
                    class="w-full flex items-center justify-between px-6 py-4 text-left font-medium text-gray-900 hover:bg-gray-50 transition-colors"
                    :aria-expanded="open === {{ $idx }}"
                    aria-controls="faq-answer-{{ $idx }}">
                    <span>{{ $faq['q'] }}</span>
                    <svg class="w-4 h-4 text-gray-500 shrink-0 ml-4 transition-transform" :class="open === {{ $idx }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="faq-answer-{{ $idx }}" x-show="open === {{ $idx }}" x-cloak class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-gray-50">
                    <p class="pt-4">{{ $faq['a'] }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-blue-700 text-white" aria-labelledby="cta-heading">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="cta-heading" class="text-3xl sm:text-4xl font-bold mb-5" style="text-wrap: balance">
            Siap Mengembangkan Bisnis Cuci Sepatu Anda?
        </h2>
        <p class="text-xl text-blue-100 mb-10">
            Daftar sekarang dan mulai kelola bisnis Anda secara profesional.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors text-base">
                Mulai Gratis Sekarang
            </a>
            <a href="{{ url('/contact') }}"
               class="inline-flex items-center justify-center px-8 py-4 border border-white/30 text-white font-medium rounded-xl hover:bg-white/10 transition-colors text-base">
                Hubungi Kami
            </a>
        </div>
        <p class="mt-8 text-sm text-blue-200">
            Pertanyaan? Email kami di
            <a href="mailto:support@stepshineworks.store" class="text-white underline hover:text-blue-100">support@stepshineworks.store</a>
        </p>
    </div>
</section>

@endsection
