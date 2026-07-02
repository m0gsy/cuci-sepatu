@extends('layouts.public')

@section('meta_title', 'Tentang Kami — StepShineWorks')
@section('meta_description', 'StepShineWorks adalah platform manajemen jasa cuci sepatu yang dibangun untuk membantu pelaku usaha shoe care di Indonesia mengelola bisnis lebih efisien.')
@section('canonical', url('/about'))
@section('og_title', 'Tentang StepShineWorks')
@section('og_description', 'Kami membangun platform manajemen cuci sepatu terbaik untuk pelaku usaha Indonesia.')
@section('og_url', url('/about'))

@section('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "Tentang StepShineWorks",
  "url": "https://stepshineworks.store/about",
  "description": "Platform manajemen jasa cuci sepatu modern untuk pelaku usaha shoe care di Indonesia.",
  "publisher": {
    "@type": "Organization",
    "name": "StepShineWorks",
    "url": "https://stepshineworks.store",
    "email": "support@stepshineworks.store"
  }
}
</script>
@endverbatim
@endsection

@section('content')

<!-- Page Header -->
<section class="bg-gray-900 text-white py-16" aria-labelledby="about-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2" role="list">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-gray-200" aria-current="page">Tentang Kami</li>
            </ol>
        </nav>
        <h1 id="about-heading" class="text-4xl sm:text-5xl font-bold mb-5">Tentang StepShineWorks</h1>
        <p class="text-xl text-gray-300 max-w-2xl" style="text-wrap: pretty">
            Kami membangun platform manajemen terbaik untuk pelaku usaha jasa cuci sepatu di Indonesia.
        </p>
    </div>
</section>

<!-- Company Introduction -->
<section class="py-16 bg-white" aria-labelledby="company-intro">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 id="company-intro" class="text-3xl font-bold text-gray-900 mb-6">Kami Ada untuk Memudahkan Bisnis Anda</h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p>
                        <strong class="text-gray-900">StepShineWorks</strong> adalah platform manajemen berbasis web yang dibangun khusus untuk membantu pelaku usaha jasa cuci sepatu mengelola operasional bisnis mereka secara lebih efisien, profesional, dan terorganisir.
                    </p>
                    <p>
                        Kami memahami tantangan yang dihadapi pemilik usaha shoe care: mencatat order manual yang rawan kelupaan, pelanggan yang bertanya-tanya soal status sepatu mereka, hingga sulitnya memantau kinerja bisnis secara akurat.
                    </p>
                    <p>
                        Dengan StepShineWorks, semua itu bisa diselesaikan dalam satu sistem terintegrasi — dari manajemen order hingga notifikasi WhatsApp otomatis ke pelanggan.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @php
                $stats = [
                    ['value' => '7', 'label' => 'Tahap tracking order', 'color' => 'bg-blue-50 text-blue-700'],
                    ['value' => 'WA', 'label' => 'Notifikasi otomatis', 'color' => 'bg-green-50 text-green-700'],
                    ['value' => '∞', 'label' => 'Data order tersimpan', 'color' => 'bg-purple-50 text-purple-700'],
                    ['value' => '24/7', 'label' => 'Akses dari mana saja', 'color' => 'bg-amber-50 text-amber-700'],
                ];
                @endphp
                @foreach($stats as $s)
                <div class="{{ $s['color'] }} rounded-2xl p-6 text-center">
                    <p class="text-4xl font-bold mb-2">{{ $s['value'] }}</p>
                    <p class="text-sm font-medium">{{ $s['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16 bg-gray-50" aria-labelledby="mission-vision">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 id="mission-vision" class="text-3xl font-bold text-gray-900 text-center mb-12">Misi &amp; Visi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-blue-600 text-white rounded-2xl p-8">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-5" aria-hidden="true">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-4">Misi Kami</h3>
                <p class="text-blue-100 leading-relaxed">
                    Memberikan platform manajemen yang mudah digunakan, terjangkau, dan lengkap — sehingga setiap pelaku usaha jasa cuci sepatu, dari skala kecil hingga besar, dapat mengelola bisnis mereka secara profesional dan efisien.
                </p>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-8">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-5" aria-hidden="true">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Visi Kami</h3>
                <p class="text-gray-600 leading-relaxed">
                    Menjadi platform manajemen pilihan utama untuk industri jasa perawatan sepatu di Indonesia, mendorong transformasi digital bisnis shoe care dari manual menjadi modern dan terukur.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-16 bg-white" aria-labelledby="layanan-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 id="layanan-heading" class="text-3xl font-bold text-gray-900 mb-4">Apa yang Kami Sediakan</h2>
            <p class="text-lg text-gray-600">Fitur lengkap untuk operasional bisnis cuci sepatu Anda.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
            $services = [
                'Manajemen order dengan detail lengkap',
                'Tracking 7 tahap status per order',
                'Notifikasi WhatsApp otomatis ke pelanggan',
                'Link status publik untuk pelanggan',
                'Laporan pendapatan & profit/loss',
                'HPP (Harga Pokok Produksi) per layanan',
                'Sistem poin & reward pelanggan',
                'Manajemen voucher diskon',
                'Pemantauan stok bahan cuci',
                'Catatan biaya operasional',
                'Manajemen karyawan & hak akses',
                'Template pesan WhatsApp kustomisasi',
            ];
            @endphp

            @foreach($services as $service)
            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                <div class="w-5 h-5 bg-green-100 text-green-600 rounded-full flex items-center justify-center shrink-0 mt-0.5" aria-hidden="true">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-700 font-medium">{{ $service }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-gray-50" aria-labelledby="why-us">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 id="why-us" class="text-3xl font-bold text-gray-900 mb-4">Mengapa Memilih StepShineWorks?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @php
            $reasons = [
                ['title' => 'Dibuat Khusus untuk Shoe Care', 'desc' => 'Bukan software umum yang dimodifikasi. StepShineWorks dirancang dari awal untuk kebutuhan bisnis cuci sepatu, dengan alur kerja yang sesuai dengan operasional nyata.'],
                ['title' => 'Notifikasi WhatsApp Real-Time', 'desc' => 'Tidak perlu kirim pesan manual. Sistem mengirim notifikasi ke pelanggan secara otomatis di setiap perubahan status order.'],
                ['title' => 'Mudah Digunakan', 'desc' => 'Antarmuka yang intuitif dan ramah pengguna. Karyawan baru pun bisa langsung menggunakannya tanpa pelatihan panjang.'],
                ['title' => 'Laporan Bisnis Akurat', 'desc' => 'Pantau kesehatan bisnis Anda dengan laporan pendapatan, profit/loss, HPP, dan operasional yang akurat dan real-time.'],
            ];
            @endphp

            @foreach($reasons as $r)
            <div class="bg-white rounded-2xl border border-gray-100 p-7">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    {{ $r['title'] }}
                </h3>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $r['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-blue-700 text-white" aria-labelledby="about-cta">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 id="about-cta" class="text-3xl font-bold mb-5">Mulai Perjalanan Digital Bisnis Anda</h2>
        <p class="text-blue-100 mb-8 text-lg">Ada pertanyaan? Tim kami siap membantu Anda memulai.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors">
                Hubungi Kami
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-7 py-3.5 border border-white/30 text-white font-medium rounded-xl hover:bg-white/10 transition-colors">
                Masuk ke Sistem
            </a>
        </div>
    </div>
</section>

@endsection
