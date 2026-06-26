@extends('layouts.app')
@section('title', 'Biaya operasional')

@section('header-actions')
<button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
        class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
    + Catat biaya
</button>
@endsection

@section('content')
<div class="space-y-5">

{{-- Pilih bulan --}}
<form method="GET" class="flex items-center gap-3">
    <input type="month" name="bulan" value="{{ $bulan }}"
           class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
           onchange="this.form.submit()">
    @if(request('kategori'))
    <a href="{{ route('operasional.index', ['bulan' => $bulan]) }}"
       class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-2 rounded-lg">Reset filter</a>
    @endif
</form>

{{-- Ringkasan --}}
<div class="grid grid-cols-3 gap-3">
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Pendapatan</p>
        <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($pendapatan, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Total biaya</p>
        <p class="text-2xl font-semibold text-red-700">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-2">Keuntungan bersih (est.)</p>
        @php $laba = $pendapatan - $totalBiaya; @endphp
        <p class="text-2xl font-semibold {{ $laba >= 0 ? 'text-green-700' : 'text-red-700' }}">
            Rp {{ number_format(abs($laba), 0, ',', '.') }}
            @if($laba < 0)<span class="text-sm font-normal">(rugi)</span>@endif
        </p>
    </div>
</div>

<div class="grid grid-cols-3 gap-5">

    {{-- Rekap per kategori --}}
    <div class="col-span-1">
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Per kategori</p>
            </div>
            @forelse($rekapKategori as $r)
            <a href="{{ route('operasional.index', ['bulan' => $bulan, 'kategori' => $r->kategori]) }}"
               class="flex items-center justify-between px-5 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors last:border-0">
                <span class="text-sm text-gray-700">{{ ucfirst($r->kategori) }}</span>
                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($r->total, 0, ',', '.') }}</span>
            </a>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-400">Belum ada data.</p>
            @endforelse
        </div>
    </div>

    {{-- Daftar biaya --}}
    <div class="col-span-2">
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-900">
                    Daftar biaya
                    @if(request('kategori'))
                    <span class="text-gray-400 font-normal">— {{ ucfirst(request('kategori')) }}</span>
                    @endif
                </p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Kategori</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($operasionals as $o)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ $o->nama }}</p>
                            @if($o->catatan)<p class="text-xs text-gray-400">{{ $o->catatan }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $o->kategori_badge }}">
                                {{ $o->kategori_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-right text-red-700">
                            Rp {{ number_format($o->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $o->tanggal->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('operasional.destroy', $o) }}"
                                  onsubmit="return confirm('Hapus biaya ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada biaya dicatat bulan ini.</td></tr>
                @endforelse
                </tbody>
            </table>
            @if($operasionals->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $operasionals->links() }}</div>
            @endif
        </div>
    </div>
</div>

</div>

{{-- Modal tambah biaya --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-100 p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Catat biaya operasional</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
        </div>
        <form method="POST" action="{{ route('operasional.store') }}">
            @csrf
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nama biaya</label>
                    <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Listrik bulan ini" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Kategori</label>
                        <select name="kategori" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="bahan">Bahan</option>
                            <option value="utilitas">Utilitas</option>
                            <option value="gaji">Gaji</option>
                            <option value="sewa">Sewa</option>
                            <option value="peralatan">Peralatan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" min="1"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="150000" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="flex-1 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="flex-1 py-2.5 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection
