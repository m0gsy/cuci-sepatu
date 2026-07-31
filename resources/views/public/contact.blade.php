@extends('layouts.public')

@section('meta_title', 'Kontak Kami — StepShineWorks')
@section('meta_description', 'Hubungi tim StepShineWorks. Email: support@stepshineworks.store. Kami siap membantu Anda memulai atau menjawab pertanyaan tentang platform manajemen cuci sepatu kami.')
@section('canonical', url('/contact'))
@section('og_title', 'Kontak StepShineWorks')
@section('og_description', 'Hubungi tim kami untuk pertanyaan, bantuan, atau demo platform manajemen cuci sepatu.')
@section('og_url', url('/contact'))

@section('schema')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ContactPage",
  "name": "Kontak StepShineWorks",
  "url": "https://stepshineworks.store/contact",
  "description": "Halaman kontak untuk menghubungi tim StepShineWorks.",
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
<section class="bg-gray-900 text-white py-16" aria-labelledby="contact-heading">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2" role="list">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-gray-200" aria-current="page">Kontak</li>
            </ol>
        </nav>
        <h1 id="contact-heading" class="text-4xl sm:text-5xl font-bold mb-5">Hubungi Kami</h1>
        <p class="text-xl text-gray-300 max-w-xl">
            Tim kami siap membantu Anda. Kirim pesan dan kami akan merespons dalam 1x24 jam.
        </p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">

            <!-- Contact Info -->
            <aside class="lg:col-span-2 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Informasi Kontak</h2>
                </div>

                <!-- Email -->
                <div class="flex gap-4 p-5 bg-blue-50 rounded-xl">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">Email Support</p>
                        <a href="mailto:support@stepshineworks.store" class="text-sm text-gray-900 hover:text-blue-600 font-medium transition-colors break-all">
                            support@stepshineworks.store
                        </a>
                        <p class="text-xs text-gray-500 mt-1">Respons dalam 1x24 jam kerja</p>
                    </div>
                </div>

                <!-- Telepon / CS -->
                <div class="flex gap-4 p-5 bg-green-50 rounded-xl">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-lg flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-1">Telepon / CS</p>
                        <p class="text-sm text-gray-900 font-medium">0819-5880-0679</p>
                        <p class="text-xs text-gray-500 mt-1">Senin – Sabtu, 08.00 – 17.00 WIB</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="flex gap-4 p-5 bg-gray-50 rounded-xl">
                    <div class="w-10 h-10 bg-gray-700 text-white rounded-lg flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Alamat</p>
                        <p class="text-sm text-gray-900 font-medium">Indonesia</p>
                        <p class="text-xs text-gray-500 mt-1">Jawa Tengah, Indonesia</p>
                    </div>
                </div>

                <!-- Business Hours -->
                <div class="border border-gray-100 rounded-xl p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jam Operasional
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Senin – Jumat</dt>
                            <dd class="text-gray-900 font-medium">08.00 – 17.00 WIB</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sabtu</dt>
                            <dd class="text-gray-900 font-medium">09.00 – 14.00 WIB</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Minggu &amp; Hari Libur</dt>
                            <dd class="text-gray-500">Tutup</dd>
                        </div>
                    </dl>
                </div>
            </aside>

            <!-- Contact Form -->
            <div class="lg:col-span-3">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Kirim Pesan</h2>

                @if(session('contact_success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-5 mb-6 flex items-start gap-3" role="alert">
                    <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Pesan berhasil terkirim!</p>
                        <p class="text-sm mt-1">Terima kasih telah menghubungi kami. Tim kami akan merespons dalam 1x24 jam kerja.</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-5" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nama Lengkap <span class="text-red-500" aria-label="wajib diisi">*</span>
                            </label>
                            <input type="text"
                                   id="contact_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('name') border-red-400 @enderror"
                                   placeholder="John Doe"
                                   aria-describedby="@error('name') name-error @enderror">
                            @error('name')
                            <p id="name-error" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Alamat Email <span class="text-red-500" aria-label="wajib diisi">*</span>
                            </label>
                            <input type="email"
                                   id="contact_email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('email') border-red-400 @enderror"
                                   placeholder="email@domain.com"
                                   aria-describedby="@error('email') email-error @enderror">
                            @error('email')
                            <p id="email-error" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="contact_subject" class="block text-sm font-medium text-gray-700 mb-1.5">Subjek</label>
                        <input type="text"
                               id="contact_subject"
                               name="subject"
                               value="{{ old('subject') }}"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                               placeholder="Pertanyaan tentang fitur...">
                    </div>

                    <div>
                        <label for="contact_message" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Pesan <span class="text-red-500" aria-label="wajib diisi">*</span>
                        </label>
                        <textarea id="contact_message"
                                  name="message"
                                  required
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none @error('message') border-red-400 @enderror"
                                  placeholder="Tuliskan pesan Anda di sini..."
                                  aria-describedby="@error('message') message-error @enderror">{{ old('message') }}</textarea>
                        @error('message')
                        <p id="message-error" class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start gap-2">
                        <p class="text-xs text-gray-500">
                            Dengan mengirim formulir ini, Anda menyetujui
                            <a href="{{ url('/privacy-policy') }}" class="text-blue-600 hover:underline">Kebijakan Privasi</a>
                            kami.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection
