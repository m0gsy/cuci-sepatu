<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Order — {{ $order->no_order }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            $statusConfig = [
                'diterima'     => ['bg' => 'bg-slate-50',   'text' => 'text-slate-800',  'label' => 'Order diterima',   'desc' => 'Order Anda sudah kami terima dan sedang dalam antrian'],
                'inspeksi'     => ['bg' => 'bg-purple-50',  'text' => 'text-purple-800', 'label' => 'Inspeksi',         'desc' => 'Kami sedang memeriksa kondisi sepatu Anda'],
                'dicuci'       => ['bg' => 'bg-blue-50',    'text' => 'text-blue-800',   'label' => 'Sedang dicuci',    'desc' => 'Sepatu Anda sedang dalam proses pencucian'],
                'kering'       => ['bg' => 'bg-yellow-50',  'text' => 'text-yellow-800', 'label' => 'Pengeringan',      'desc' => 'Sepatu Anda sedang dikeringkan'],
                'finishing'    => ['bg' => 'bg-orange-50',  'text' => 'text-orange-800', 'label' => 'Finishing',        'desc' => 'Tahap akhir pengerjaan sepatu Anda'],
                'siap_diambil' => ['bg' => 'bg-green-50',   'text' => 'text-green-800',  'label' => 'Siap diambil!',    'desc' => 'Sepatu Anda sudah selesai dan siap diambil'],
                'selesai'      => ['bg' => 'bg-gray-50',    'text' => 'text-gray-700',   'label' => 'Sudah Diambil',    'desc' => 'Sepatu Anda sudah diambil. Terima kasih sudah mempercayakan sepatu Anda!'],
                // legacy
                'antri'        => ['bg' => 'bg-amber-50',   'text' => 'text-amber-800',  'label' => 'Dalam antrian',    'desc' => 'Order Anda sedang menunggu giliran'],
                'proses'       => ['bg' => 'bg-blue-50',    'text' => 'text-blue-800',   'label' => 'Sedang diproses',  'desc' => 'Sepatu Anda sedang dicuci'],
                'diambil'      => ['bg' => 'bg-gray-50',    'text' => 'text-gray-700',   'label' => 'Sudah diambil',    'desc' => 'Order selesai, terima kasih!'],
            ];
            $sc = $statusConfig[$order->status] ?? $statusConfig['diterima'];

            $allStatuses = ['diterima', 'inspeksi', 'dicuci', 'kering', 'finishing', 'siap_diambil', 'selesai'];
            $labels      = ['Diterima', 'Inspeksi', 'Dicuci', 'Kering', 'Finishing', 'Siap Ambil', 'Selesai'];
            $currentIdx  = array_search($order->status, $allStatuses);
            if ($currentIdx === false) {
                // legacy mapping
                $currentIdx = match($order->status) {
                    'antri'   => 0,
                    'proses'  => 2,
                    'diambil' => 6,
                    default   => 0,
                };
            }
        @endphp

        <div class="{{ $sc['bg'] }} px-6 py-5 text-center">
            <p class="text-2xl font-bold {{ $sc['text'] }} mb-1">{{ $sc['label'] }}</p>
            <p class="text-sm {{ $sc['text'] }} opacity-80">{{ $sc['desc'] }}</p>
        </div>

        {{-- Progress bar 7 tahap --}}
        <div class="px-5 py-5 border-b border-gray-100">
            <div class="flex items-start gap-0.5">
                @foreach($allStatuses as $i => $s)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium
                                {{ $i < $currentIdx ? 'bg-brand text-white' : ($i === $currentIdx ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-400') }}">
                        @if($i < $currentIdx) ✓ @else {{ $i + 1 }} @endif
                    </div>
                    <span class="text-xs mt-1 text-center leading-tight
                                 {{ $i <= $currentIdx ? 'text-gray-700 font-medium' : 'text-gray-400' }}">
                        {{ $labels[$i] }}
                    </span>
                </div>
                @if(!$loop->last)
                <div class="h-0.5 flex-1 mt-3 {{ $i < $currentIdx ? 'bg-brand' : 'bg-gray-200' }}"></div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Detail order --}}
        <div class="px-6 py-4">
            <table class="w-full text-sm">
                @php
                    $rows = [
                        'No. order'       => $order->no_order,
                        'Pelanggan'       => $order->nama_pelanggan,
                        'Layanan'         => $order->layanan->nama,
                        'Jenis sepatu'    => $order->jenis_sepatu,
                    ];
                    if ($order->merek) $rows['Merek'] = $order->merek;
                    if ($order->warna) $rows['Warna'] = $order->warna;
                    $rows['Jumlah']         = $order->jumlah_pasang . ' pasang';
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
        Ada pertanyaan? Hubungi kami via WhatsApp
    </p>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
