@extends('layouts.public')

@section('meta_title', 'Kebijakan Privasi — StepShineWorks')
@section('meta_description', 'Kebijakan Privasi StepShineWorks menjelaskan cara kami mengumpulkan, menggunakan, dan melindungi data pribadi pengguna platform manajemen cuci sepatu kami.')
@section('canonical', url('/privacy-policy'))
@section('og_title', 'Kebijakan Privasi — StepShineWorks')
@section('og_url', url('/privacy-policy'))

@section('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Kebijakan Privasi StepShineWorks",
  "url": "https://stepshineworks.store/privacy-policy",
  "description": "Kebijakan privasi yang menjelaskan pengelolaan data pengguna platform StepShineWorks.",
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
                <li class="text-gray-200" aria-current="page">Kebijakan Privasi</li>
            </ol>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-bold mb-4">Kebijakan Privasi</h1>
        <p class="text-gray-400">Terakhir diperbarui: 1 Juli 2026</p>
    </div>
</section>

<!-- Content -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-gray max-w-none text-gray-700 leading-relaxed space-y-8">

            <!-- Intro -->
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6">
                <p class="text-sm text-blue-800">
                    StepShineWorks ("kami", "perusahaan", atau "layanan") berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat menggunakan platform kami di <a href="https://stepshineworks.store" class="text-blue-600 underline">stepshineworks.store</a>.
                </p>
            </div>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">1. Informasi yang Kami Kumpulkan</h2>
                <p class="mb-4">Kami mengumpulkan informasi dalam beberapa cara:</p>

                <h3 class="text-base font-semibold text-gray-800 mb-2">a. Informasi Akun</h3>
                <ul class="list-disc list-inside space-y-1 text-sm mb-4 pl-2">
                    <li>Nama lengkap dan alamat email</li>
                    <li>Kata sandi (dienkripsi, tidak pernah disimpan dalam bentuk terbaca)</li>
                    <li>Peran pengguna (owner, admin, karyawan)</li>
                </ul>

                <h3 class="text-base font-semibold text-gray-800 mb-2">b. Data Operasional Bisnis</h3>
                <ul class="list-disc list-inside space-y-1 text-sm mb-4 pl-2">
                    <li>Data pelanggan bisnis Anda (nama, nomor telepon, alamat)</li>
                    <li>Informasi order dan layanan</li>
                    <li>Data transaksi dan pembayaran</li>
                    <li>Riwayat komunikasi WhatsApp yang dikirim via platform</li>
                </ul>

                <h3 class="text-base font-semibold text-gray-800 mb-2">c. Data Teknis</h3>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li>Alamat IP dan informasi browser</li>
                    <li>Data sesi dan cookie otentikasi</li>
                    <li>Log aktivitas sistem untuk keamanan</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">2. Penggunaan Cookie</h2>
                <p class="text-sm mb-3">Kami menggunakan cookie untuk:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li><strong>Cookie sesi</strong> — menjaga status login Anda tetap aktif</li>
                    <li><strong>Cookie keamanan (CSRF)</strong> — melindungi formulir dari serangan lintas situs</li>
                    <li><strong>Cookie preferensi</strong> — menyimpan pengaturan tampilan Anda</li>
                </ul>
                <p class="text-sm mt-3">Kami tidak menggunakan cookie pelacakan pihak ketiga untuk keperluan iklan.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">3. Cara Kami Menggunakan Informasi Anda</h2>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Menyediakan dan mengoperasikan layanan platform</li>
                    <li>Memproses otentikasi dan keamanan akun</li>
                    <li>Mengirim notifikasi WhatsApp kepada pelanggan bisnis Anda melalui integrasi API</li>
                    <li>Menganalisis penggunaan untuk peningkatan layanan</li>
                    <li>Merespons pertanyaan dan dukungan teknis</li>
                    <li>Memenuhi kewajiban hukum yang berlaku</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">4. Akun Pengguna</h2>
                <p class="text-sm">Saat mendaftar, Anda bertanggung jawab untuk:</p>
                <ul class="list-disc list-inside space-y-1 text-sm mt-2 pl-2">
                    <li>Menjaga kerahasiaan kata sandi akun Anda</li>
                    <li>Memberikan informasi yang akurat dan terkini</li>
                    <li>Melaporkan penggunaan akun yang tidak sah kepada kami</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">5. Integrasi Pihak Ketiga</h2>
                <p class="text-sm mb-3">Platform kami menggunakan layanan pihak ketiga berikut:</p>
                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="font-semibold text-sm text-gray-900">Meta WhatsApp Business API</p>
                        <p class="text-xs text-gray-600 mt-1">Digunakan untuk mengirim notifikasi WhatsApp otomatis kepada pelanggan bisnis Anda. Data nomor telepon pelanggan dikirimkan ke API Meta untuk proses pengiriman pesan. Penggunaan data tunduk pada <a href="https://www.whatsapp.com/legal/privacy-policy" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Kebijakan Privasi WhatsApp/Meta</a>.</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="font-semibold text-sm text-gray-900">Infrastruktur Hosting</p>
                        <p class="text-xs text-gray-600 mt-1">Data disimpan di server hosting yang berlokasi di Indonesia. Kami menggunakan enkripsi HTTPS untuk semua komunikasi data.</p>
                    </div>
                </div>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">6. Penyimpanan dan Keamanan Data</h2>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li>Semua komunikasi dienkripsi menggunakan HTTPS/TLS</li>
                    <li>Kata sandi di-hash menggunakan algoritma bcrypt yang kuat</li>
                    <li>Akses data dibatasi berdasarkan peran pengguna (role-based access)</li>
                    <li>Database dilindungi dengan autentikasi dan firewall</li>
                    <li>Log aktivitas disimpan untuk keperluan keamanan dan audit</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">7. Berbagi Informasi</h2>
                <p class="text-sm mb-3">Kami <strong>tidak menjual</strong> data Anda kepada pihak ketiga. Kami hanya berbagi data dalam kondisi:</p>
                <ul class="list-disc list-inside space-y-1 text-sm pl-2">
                    <li>Diperlukan untuk penyediaan layanan (seperti pengiriman notifikasi via WhatsApp API)</li>
                    <li>Diwajibkan oleh peraturan hukum yang berlaku di Indonesia</li>
                    <li>Dengan persetujuan eksplisit Anda</li>
                </ul>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">8. Hak Pengguna</h2>
                <p class="text-sm mb-3">Sesuai peraturan perlindungan data yang berlaku, Anda memiliki hak untuk:</p>
                <ul class="list-disc list-inside space-y-2 text-sm pl-2">
                    <li><strong>Akses</strong> — meminta salinan data pribadi yang kami miliki tentang Anda</li>
                    <li><strong>Koreksi</strong> — meminta pembaruan data yang tidak akurat</li>
                    <li><strong>Penghapusan</strong> — meminta penghapusan akun dan data Anda</li>
                    <li><strong>Pembatasan</strong> — membatasi cara kami memproses data Anda</li>
                    <li><strong>Portabilitas</strong> — menerima data Anda dalam format yang dapat dibaca mesin</li>
                </ul>
                <p class="text-sm mt-3">Untuk menggunakan hak ini, hubungi kami di <a href="mailto:support@stepshineworks.store" class="text-blue-600 hover:underline">support@stepshineworks.store</a>.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">9. Retensi Data</h2>
                <p class="text-sm">Kami menyimpan data selama akun Anda aktif atau selama diperlukan untuk menyediakan layanan. Setelah akun dihapus, data disimpan selama maksimal 30 hari sebelum dihapus permanen, kecuali diwajibkan hukum untuk disimpan lebih lama.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">10. Perubahan Kebijakan Privasi</h2>
                <p class="text-sm">Kami dapat memperbarui Kebijakan Privasi ini sewaktu-waktu. Perubahan material akan kami beritahukan melalui email atau notifikasi dalam platform. Penggunaan layanan yang berlanjut setelah pembaruan dianggap sebagai persetujuan atas kebijakan yang baru.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">11. Hukum yang Berlaku</h2>
                <p class="text-sm">Kebijakan Privasi ini diatur dan ditafsirkan berdasarkan hukum Indonesia, termasuk Undang-Undang Perlindungan Data Pribadi (UU PDP) yang berlaku.</p>
            </article>

            <article>
                <h2 class="text-xl font-bold text-gray-900 mb-4">12. Hubungi Kami</h2>
                <p class="text-sm mb-4">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami:</p>
                <div class="bg-gray-50 rounded-xl p-5">
                    <p class="text-sm font-semibold text-gray-900">StepShineWorks</p>
                    <p class="text-sm text-gray-600 mt-1">Email: <a href="mailto:support@stepshineworks.store" class="text-blue-600 hover:underline">support@stepshineworks.store</a></p>
                    <p class="text-sm text-gray-600">Website: <a href="https://stepshineworks.store" class="text-blue-600 hover:underline">https://stepshineworks.store</a></p>
                    <p class="text-sm text-gray-600">Indonesia</p>
                </div>
            </article>

        </div>

        <!-- Quick Links -->
        <div class="mt-12 pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500 mb-4">Halaman terkait:</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ url('/terms') }}" class="text-sm text-blue-600 hover:underline">Syarat &amp; Ketentuan</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/refund-policy') }}" class="text-sm text-blue-600 hover:underline">Kebijakan Pengembalian</a>
                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                <a href="{{ url('/contact') }}" class="text-sm text-blue-600 hover:underline">Kontak Kami</a>
            </div>
        </div>
    </div>
</section>

@endsection
