<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Order — {{ $order->no_order }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#1a3a2a',
                            hover: '#142d20'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
<div class="w-full max-w-md">

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-brand rounded-xl mb-3">
            <span class="text-white font-bold text-sm">SS</span>
        </div>
        <h1 class="text-lg font-semibold text-gray-900">Step Shine Works</h1>
        <p class="text-sm text-gray-500">Tracking order Anda</p>
    </div>

    {{-- Card order --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">

        {{-- Header status --}}
        @php
            $normalizedStatus = $order->status;
            if (in_array($normalizedStatus, ['antri'])) {
                $normalizedStatus = 'menunggu_pembayaran';
            } elseif (in_array($normalizedStatus, ['proses', 'diterima', 'inspeksi', 'dicuci', 'kering', 'finishing'])) {
                $normalizedStatus = 'diproses';
            } elseif (in_array($normalizedStatus, ['diambil'])) {
                $normalizedStatus = 'selesai';
            }

            $statusConfig = [
                'draft'               => ['bg' => 'bg-slate-50',   'text' => 'text-slate-800',  'label' => 'Draft',            'desc' => 'Order Anda masih berupa draft awal'],
                'menunggu_pembayaran' => ['bg' => 'bg-amber-50',   'text' => 'text-amber-800',  'label' => 'Menunggu Pembayaran', 'desc' => 'Menunggu pembayaran selesai sebelum diproses'],
                'diproses'            => ['bg' => 'bg-blue-50',    'text' => 'text-blue-800',   'label' => 'Sedang Diproses',   'desc' => 'Sepatu Anda sedang dalam pengerjaan oleh tim kami'],
                'siap_diambil'        => ['bg' => 'bg-green-50',   'text' => 'text-green-800',  'label' => 'Siap Diambil!',    'desc' => 'Sepatu Anda sudah selesai dan siap untuk diambil'],
                'selesai'             => ['bg' => 'bg-gray-50',    'text' => 'text-gray-700',   'label' => 'Sudah Diambil',    'desc' => 'Sepatu Anda sudah diambil. Terima kasih sudah mempercayakan sepatu Anda!'],
                'batal'               => ['bg' => 'bg-red-50',     'text' => 'text-red-800',    'label' => 'Batal',            'desc' => 'Order ini telah dibatalkan'],
            ];

            if ($order->poin > 0) {
                $statusConfig['siap_diambil']['desc'] .= ' (Anda mendapatkan ' . $order->poin . ' poin!)';
                $statusConfig['selesai']['desc'] .= ' (Anda mendapatkan ' . $order->poin . ' poin!)';
            }

            $sc = $statusConfig[$normalizedStatus] ?? $statusConfig['draft'];

            $allStatuses = ['draft', 'menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai'];
            $labels      = ['Draft', 'Menunggu Pembayaran', 'Diproses', 'Siap Diambil', 'Selesai'];
            $currentIdx  = array_search($normalizedStatus, $allStatuses);
        @endphp

        <div class="{{ $sc['bg'] }} px-6 py-5 text-center">
            <p class="text-2xl font-bold {{ $sc['text'] }} mb-1">{{ $sc['label'] }}</p>
            <p class="text-sm {{ $sc['text'] }} opacity-80">{{ $sc['desc'] }}</p>
        </div>

        {{-- Progress bar 5 tahap --}}
        @if($normalizedStatus !== 'batal' && $currentIdx !== false)
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="relative flex items-center w-full">
                <!-- Background line -->
                <div class="absolute left-[10%] right-[10%] top-3 h-0.5 bg-gray-200">
                    @php
                        $percent = ($currentIdx / (count($allStatuses) - 1)) * 100;
                    @endphp
                    <div class="h-full bg-brand transition-all duration-500" style="width: {{ $percent }}%"></div>
                </div>

                @foreach($allStatuses as $i => $s)
                @php
                    $done = $i <= $currentIdx;
                    $curr = $i === $currentIdx;
                @endphp
                <div class="flex-1 flex flex-col items-center relative z-10">
                    <div class="bg-white px-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold
                                    {{ $curr ? 'bg-white text-brand ring-2 ring-brand shadow-sm' : ($done ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400') }}">
                            @if($done && !$curr)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            {{ $i + 1 }}
                            @endif
                        </div>
                    </div>
                    <span class="text-[9px] text-center mt-1.5 leading-tight font-semibold text-gray-400 {{ $done ? 'text-gray-700' : '' }} min-w-[65px] max-w-[85px] break-words">
                        {{ str_replace('\n', ' ', $labels[$i]) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Detail order --}}
        <div class="px-6 py-4">
            <table class="w-full text-sm">
                @php
                    $rows = [
                        'No. order'       => $order->no_order,
                        'Pelanggan'       => $order->nama_pelanggan,
                        'Layanan'         => $order->items->isNotEmpty()
                            ? $order->items->map(fn($i) => $i->layanan->nama ?? '—')->join(', ')
                            : ($order->layanan->nama ?? '—'),
                        'Jenis sepatu'    => $order->items->isNotEmpty()
                            ? $order->items->map(fn($i) => $i->jenisBarang->nama ?? '—')->join(', ')
                            : ($order->jenis_sepatu ?? '—'),
                    ];
                    if ($order->items->isNotEmpty()) {
                        $mereks = $order->items->map(fn($i) => $i->merek)->filter()->join(', ');
                        $warnas = $order->items->map(fn($i) => $i->warna)->filter()->join(', ');
                        if ($mereks) $rows['Merek'] = $mereks;
                        if ($warnas) $rows['Warna'] = $warnas;
                    } else {
                        if ($order->merek) $rows['Merek'] = $order->merek;
                        if ($order->warna) $rows['Warna'] = $order->warna;
                    }
                    $rows['Jumlah']         = $order->jumlah_pasang . ' pasang';
                    if ($order->diskon > 0) {
                        $rows['Diskon voucher'] = '- Rp ' . number_format($order->diskon, 0, ',', '.');
                    }
                    if ($order->diskon_poin > 0) {
                        $rows['Diskon poin'] = '- Rp ' . number_format($order->diskon_poin, 0, ',', '.') . ' (' . $order->poin_digunakan . ' poin)';
                    }
                    $rows['Total']          = 'Rp ' . number_format($order->pembayaran?->total ?? 0, 0, ',', '.');
                    $rows['Est. selesai']   = $order->estimasi_selesai?->isoFormat('D MMMM Y') ?? '—';
                    if ($order->selesai_pada) {
                        $rows['Selesai pada'] = $order->selesai_pada->isoFormat('D MMM Y, HH:mm');
                    }
                @endphp
                @foreach($rows as $label => $value)
                <tr class="border-b border-gray-50 last:border-0">
                    <td class="py-2.5 text-gray-400 w-36 text-xs">{{ $label }}</td>
                    <td class="py-2.5 font-medium text-gray-900">{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- Form review (hanya jika order selesai & belum review) --}}
        @if(in_array($order->status, ['siap_diambil', 'selesai', 'diambil']) && !$order->review)
        <div class="px-6 pb-5 border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-700 mb-3">Berikan ulasan Anda</p>
            <form method="POST" action="{{ route('orders.review.store', $order) }}"
                  x-data="{ rating: 0 }">
                @csrf
                <div class="flex gap-1 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" @click="rating = {{ $i }}"
                            class="text-2xl transition-colors"
                            :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">★</button>
                    @endfor
                    <input type="hidden" name="rating" :value="rating">
                </div>
                <textarea name="ulasan" rows="2" placeholder="Tulis ulasan Anda... (opsional)"
                          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm mb-2 resize-none"></textarea>
                <button type="submit" x-bind:disabled="rating === 0"
                        class="w-full py-2 text-sm bg-brand text-white rounded-lg disabled:opacity-40">
                    Kirim ulasan
                </button>
            </form>
        </div>
        @elseif($order->review)
        <div class="px-6 pb-5 border-t border-gray-100 pt-4">
            <p class="text-xs text-gray-400">Ulasan Anda:
                <span class="text-yellow-400">{{ $order->review->bintang }}</span>
            </p>
            @if($order->review->ulasan)
            <p class="text-sm text-gray-600 mt-1">"{{ $order->review->ulasan }}"</p>
            @endif
        </div>
        @endif

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-500">
                Terakhir diperbarui: {{ now()->isoFormat('D MMM Y, HH:mm') }}
            </p>
            <button onclick="location.reload()"
                    class="mt-2 text-xs text-gray-600 hover:text-gray-900 underline">
                Refresh status
            </button>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">
        Ada pertanyaan? <a href="https://wa.me/6281958800679?text=Halo%20Step%20Shine%20Works%2C%20saya%20ingin%20bertanya%20tentang%20order%20{{ $order->no_order }}" target="_blank" class="text-brand hover:underline font-medium">Hubungi kami via WhatsApp</a>
    </p>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
