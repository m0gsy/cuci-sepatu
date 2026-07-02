@extends('layouts.public')

@section('meta_title', 'Kebijakan Pengembalian — StepShineWorks')
@section('meta_description', 'Kebijakan pengembalian dan refund StepShineWorks. Informasi lengkap tentang syarat, proses, dan jangka waktu pengembalian layanan platform manajemen cuci sepatu.')
@section('canonical', url('/refund-policy'))
@section('og_title', 'Kebijakan Pengembalian — StepShineWorks')
@section('og_url', url('/refund-policy'))

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Kebijakan Pengembalian StepShineWorks",
  "url": "https://stepshineworks.store/refund-policy",
  "description": "Kebijakan pengembalian dana untuk layanan platform manajemen cuci sepatu StepShineWorks.",
  "publisher": {
    "@type": "Organization",
    "name": "StepShineWorks",
    "url": "https://stepshineworks.store"
  }
}
</script>
@endsection

@section('content')

<!-- Page Header -->
<section class="bg-gray-900 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2" role="list">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-gray-200" aria-current="page">Kebijakan Pengembalian</li>
            </ol>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-bold mb-4">Kebijakan Pengembalian</h1>
        <p class="text-gray-400">Terakhir diperbarui: 1 Juli 2026</p>
    </div>
</section>

<!-- Content -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-8 text-gray-700 leading-relaxed">

            <!-- Intro -->
            <div class="bg-green-50 border border-green-100 rounded-xl p-6">
                <p class="text-sm text-green-800">
                    Kami berkomitmen untuk memastikan kepuasan Anda dalam menggunakan platform StepShineWorks. Kebijakan pengembalian ini menjelaskan kondisi dan proses pengembalian dana apabila Anda tidak puas dengan layanan kami.
                </p>
            </div>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">1. Ruang Lingkup Kebijakan</h2>
                <p class="text-sm">Kebijakan pengembalian ini berlaku untuk biaya berlangganan atau pembayaran yang dilakukan kepada StepShineWorks untuk layanan platform manajemen cuci sepatu. Kebijakan ini <strong>tidak berlaku</strong> untuk data yang telah diproses melalui integrasi pihak ketiga (seperti pengiriman pesan WhatsApp).</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">2. Syarat Pengembalian Dana</h2>
                <p class="text-sm mb-4">Anda berhak mengajukan pengembalian dana dalam kondisi berikut:</p>

                <div class="space-y-4">
                    <div class="border border-green-200 bg-green-50 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-green-800 mb-2">✓ Pengembalian Penuh (100%)</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-green-700 pl-2">
                            <li>Layanan tidak tersedia (downtime) lebih dari 48 jam berturut-turut yang disebabkan oleh kesalahan kami</li>
                            <li>Permintaan pembatalan dalam 7 hari pertama sejak aktivasi (uji coba tidak memuaskan)</li>
                            <li>Terjadi kesalahan tagih (charged double atau salah nominal)</li>
                        </ul>
                    </div>

                    <div class="border border-amber-200 bg-amber-50 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-amber-800 mb-2">~ Pengembalian Prorata</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-amber-700 pl-2">
                            <li>Pembatalan di tengah periode berlangganan (dikembalikan sisa hari yang belum terpakai)</li>
                            <li>Layanan terdegradasi secara signifikan untuk periode tertentu akibat kesalahan kami</li>
                        </ul>
                    </div>

                    <div class="border border-red-200 bg-red-50 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-red-800 mb-2">✗ Tidak Dapat Dikembalikan</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700 pl-2">
                            <li>Biaya layanan yang telah digunakan sepenuhnya</li>
                            <li>Penghentian akun akibat pelanggaran Syarat dan Ketentuan</li>
                            <li>Ketidakpuasan yang disebabkan faktor di luar kendali kami (gangguan internet, dll.)</li>
                            <li>Biaya layanan pihak ketiga yang sudah terpakai (biaya API WhatsApp, dll.)</li>
                        </ul>
                    </div>
                </div>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">3. Proses Pengajuan Pengembalian</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                    $steps = [
                        ['num' => '1', 'title' => 'Hubungi Kami', 'desc' => 'Kirim email ke support@stepshineworks.store dengan subjek "Permohonan Refund - [Nama Bisnis Anda]"'],
                        ['num' => '2', 'title' => 'Verifikasi', 'desc' => 'Tim kami akan memverifikasi identitas dan alasan pengajuan dalam 2-3 hari kerja'],
                        ['num' => '3', 'title' => 'Proses Dana', 'desc' => 'Jika disetujui, dana dikembalikan dalam 7-14 hari kerja melalui metode pembayaran asal'],
                    ];
                    @endphp
                    @foreach($steps as $s)
                    <div class="bg-gray-50 rounded-xl p-5 text-center">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-3" aria-label="Langkah {{ $s['num'] }}">{{ $s['num'] }}</div>
                        <h3 class="font-semibold text-gray-900 text-sm mb-2">{{ $s['title'] }}</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $s['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">4. Informasi yang Diperlukan</h2>
                <p class="text-sm mb-3">Saat mengajukan permohonan, sertakan:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li>Nama lengkap dan alamat email akun terdaftar</li>
                    <li>Nama bisnis</li>
                    <li>Tanggal dan nominal pembayaran</li>
                    <li>Nomor bukti transaksi (jika ada)</li>
                    <li>Alasan permohonan pengembalian secara detail</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">5. Batas Waktu Pengajuan</h2>
                <p class="text-sm">Permohonan pengembalian harus diajukan paling lambat <strong>30 hari</strong> sejak tanggal transaksi. Permohonan yang diajukan setelah batas waktu tersebut tidak dapat diproses.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">6. Pengecualian dan Kondisi Khusus</h2>
                <p class="text-sm">Dalam kondisi tertentu yang berada di luar kebijakan standar ini, kami dapat mempertimbangkan pengajuan Anda secara kasuistik. Keputusan akhir ada di tangan tim manajemen StepShineWorks dan bersifat final.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">7. Hubungi Kami</h2>
                <p class="text-sm mb-4">Untuk permohonan pengembalian atau pertanyaan tentang kebijakan ini:</p>
                <div class="bg-gray-50 rounded-xl p-5">
                    <p class="text-sm font-semibold text-gray-900">StepShineWorks — Tim Dukungan</p>
                    <p class="text-sm text-gray-600 mt-2">Email: <a href="mailto:support@stepshineworks.store" class="text-blue-600 hover:underline">support@stepshineworks.store</a></p>
                    <p class="text-sm text-gray-600">Subjek: <span class="font-medium">Permohonan Refund - [Nama Bisnis Anda]</span></p>
                    <p class="text-sm text-gray-500 mt-2">Jam kerja: Senin – Jumat, 08.00 – 17.00 WIB</p>
                </div>
            </article>

        </div>

        <!-- Quick Links -->
        <div class="mt-12 pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500 mb-4">Halaman terkait:</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ url('/privacy-policy') }}" class="text-sm text-blue-600 hover:underline">Kebijakan Privasi</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/terms') }}" class="text-sm text-blue-600 hover:underline">Syarat &amp; Ketentuan</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/contact') }}" class="text-sm text-blue-600 hover:underline">Kontak Kami</a>
            </div>
        </div>
    </div>
</section>

@endsection
