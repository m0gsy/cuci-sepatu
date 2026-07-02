@extends('layouts.public')

@section('meta_title', 'Syarat & Ketentuan — StepShineWorks')
@section('meta_description', 'Syarat dan Ketentuan penggunaan platform StepShineWorks. Baca ketentuan layanan, kewajiban pengguna, pembayaran, dan hukum yang berlaku sebelum menggunakan layanan kami.')
@section('canonical', url('/terms'))
@section('og_title', 'Syarat & Ketentuan — StepShineWorks')
@section('og_url', url('/terms'))

@section('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Syarat dan Ketentuan StepShineWorks",
  "url": "https://stepshineworks.store/terms",
  "description": "Syarat dan ketentuan penggunaan platform manajemen cuci sepatu StepShineWorks.",
  "publisher": {
    "@type": "Organization",
    "name": "StepShineWorks",
    "url": "https://stepshineworks.store"
  }
}
</script>
@endverbatim
@endsection

@section('content')

<!-- Page Header -->
<section class="bg-gray-900 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2" role="list">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-gray-200" aria-current="page">Syarat &amp; Ketentuan</li>
            </ol>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-bold mb-4">Syarat &amp; Ketentuan</h1>
        <p class="text-gray-400">Terakhir diperbarui: 1 Juli 2026</p>
    </div>
</section>

<!-- Content -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-8 text-gray-700 leading-relaxed">

            <!-- Intro -->
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-6">
                <p class="text-sm text-amber-800">
                    Dengan mengakses atau menggunakan platform StepShineWorks di <a href="https://stepshineworks.store" class="text-amber-700 underline font-medium">stepshineworks.store</a>, Anda menyetujui Syarat dan Ketentuan ini secara keseluruhan. Jika Anda tidak setuju, mohon untuk tidak menggunakan layanan kami.
                </p>
            </div>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">1. Deskripsi Layanan</h2>
                <p class="text-sm">StepShineWorks adalah platform manajemen berbasis web yang dirancang untuk membantu pelaku usaha jasa cuci sepatu mengelola operasional bisnis mereka, termasuk namun tidak terbatas pada: manajemen order, tracking status, notifikasi WhatsApp, laporan bisnis, manajemen pelanggan, stok bahan, dan karyawan.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">2. Akun Pengguna</h2>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Anda harus mendaftarkan akun untuk menggunakan fitur platform</li>
                    <li>Anda bertanggung jawab penuh atas keamanan kata sandi dan aktivitas akun Anda</li>
                    <li>Informasi akun harus akurat, lengkap, dan terkini</li>
                    <li>Satu akun bisnis dapat memiliki beberapa pengguna dengan peran berbeda (owner, admin, karyawan)</li>
                    <li>Kami berhak menangguhkan atau menghentikan akun yang melanggar ketentuan ini</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">3. Penggunaan Layanan yang Diizinkan</h2>
                <p class="text-sm mb-3">Anda boleh menggunakan platform ini untuk:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2 mb-4">
                    <li>Mengelola operasional bisnis jasa cuci sepatu Anda yang sah</li>
                    <li>Mengirim komunikasi bisnis yang relevan kepada pelanggan Anda</li>
                    <li>Menganalisis data bisnis Anda sendiri</li>
                </ul>
                <p class="text-sm mb-3">Anda <strong>dilarang</strong> menggunakan platform ini untuk:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li>Mengirim spam atau pesan massal yang tidak diminta</li>
                    <li>Menyimpan atau memproses data yang melanggar privasi pihak ketiga</li>
                    <li>Aktivitas yang melanggar hukum di Indonesia</li>
                    <li>Merusak, membebani, atau menganggu infrastruktur platform</li>
                    <li>Meniru identitas pihak lain</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">4. Layanan Berbasis Langganan</h2>
                <p class="text-sm">Detail paket harga, pembayaran, dan pembaruan langganan akan diinformasikan secara terpisah atau dapat ditanyakan langsung ke tim kami di <a href="mailto:support@stepshineworks.store" class="text-blue-600 hover:underline">support@stepshineworks.store</a>. Ketentuan pembayaran yang disepakati saat pendaftaran merupakan bagian dari perjanjian layanan ini.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">5. Pembatalan dan Penghentian</h2>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Anda dapat menghentikan penggunaan layanan kapan saja dengan menghubungi kami</li>
                    <li>Kami berhak menghentikan akses tanpa pemberitahuan jika terjadi pelanggaran material terhadap ketentuan ini</li>
                    <li>Setelah penghentian, data bisnis Anda dapat diekspor dalam waktu 30 hari sebelum dihapus permanen</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">6. Integrasi WhatsApp Business API</h2>
                <p class="text-sm mb-3">Fitur notifikasi WhatsApp menggunakan WhatsApp Business API (Meta Platforms). Dengan menggunakan fitur ini, Anda menyatakan bahwa:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li>Anda telah mendapatkan persetujuan dari penerima pesan untuk menerima komunikasi bisnis</li>
                    <li>Pesan yang dikirim bersifat transaksional dan relevan dengan layanan yang dipesan pelanggan</li>
                    <li>Anda mematuhi <a href="https://www.whatsapp.com/legal/business-policy" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Kebijakan Bisnis WhatsApp</a></li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">7. Kepemilikan Konten &amp; Kekayaan Intelektual</h2>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Data bisnis yang Anda masukkan ke platform (order, pelanggan, dll.) tetap menjadi milik Anda</li>
                    <li>Platform StepShineWorks, kode, desain, dan merek dagang adalah milik eksklusif kami</li>
                    <li>Anda tidak diizinkan menyalin, mendistribusikan, atau memodifikasi platform tanpa izin tertulis</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">8. Batasan Tanggung Jawab</h2>
                <p class="text-sm mb-3">Sejauh yang diizinkan hukum:</p>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Layanan disediakan "sebagaimana adanya" tanpa jaminan apapun</li>
                    <li>Kami tidak bertanggung jawab atas kerugian tidak langsung, insidental, atau konsekuensial</li>
                    <li>Tanggung jawab total kami tidak melebihi jumlah yang Anda bayarkan dalam 3 bulan terakhir</li>
                    <li>Kami tidak menjamin ketersediaan layanan 100% (uptime) namun berupaya semaksimal mungkin</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">9. Perubahan Layanan</h2>
                <p class="text-sm">Kami berhak mengubah, memperbarui, atau menghentikan fitur layanan kapan saja. Perubahan signifikan akan diinformasikan minimal 14 hari sebelumnya melalui email atau notifikasi dalam platform.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">10. Hukum yang Berlaku &amp; Yurisdiksi</h2>
                <p class="text-sm">Syarat dan Ketentuan ini diatur oleh dan ditafsirkan berdasarkan hukum Republik Indonesia. Setiap perselisihan yang timbul akan diselesaikan melalui musyawarah mufakat, dan apabila tidak tercapai, akan diselesaikan melalui pengadilan yang berwenang di Indonesia.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">11. Hubungi Kami</h2>
                <p class="text-sm mb-4">Pertanyaan tentang Syarat dan Ketentuan ini dapat diajukan ke:</p>
                <div class="bg-gray-50 rounded-xl p-5">
                    <p class="text-sm font-semibold text-gray-900">StepShineWorks</p>
                    <p class="text-sm text-gray-600 mt-1">Email: <a href="mailto:support@stepshineworks.store" class="text-blue-600 hover:underline">support@stepshineworks.store</a></p>
                    <p class="text-sm text-gray-600">Website: <a href="https://stepshineworks.store" class="text-blue-600 hover:underline">https://stepshineworks.store</a></p>
                </div>
            </article>

        </div>

        <!-- Quick Links -->
        <div class="mt-12 pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500 mb-4">Halaman terkait:</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ url('/privacy-policy') }}" class="text-sm text-blue-600 hover:underline">Kebijakan Privasi</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/refund-policy') }}" class="text-sm text-blue-600 hover:underline">Kebijakan Pengembalian</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/contact') }}" class="text-sm text-blue-600 hover:underline">Kontak Kami</a>
            </div>
        </div>
    </div>
</section>

@endsection
