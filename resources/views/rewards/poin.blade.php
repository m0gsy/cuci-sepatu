@extends('layouts.app')
@section('title', 'Kelola Poin Pelanggan')

@section('header-actions')
<a href="{{ route('rewards.index') }}"
   class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50">
    ← Daftar reward
</a>
@endsection

@section('content')
<div class="space-y-5">
<div class="grid grid-cols-3 gap-5">

    {{-- Form tambah/kurangi poin --}}
    <div class="col-span-1">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-sm font-semibold text-gray-900 mb-4">Atur poin manual</p>
            <form method="POST" action="{{ route('rewards.poin.tambah') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Pelanggan <span class="text-red-400">*</span></label>
                    <select name="pelanggan_id" required
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Pilih pelanggan...</option>
                        @foreach($pelanggans as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->poin }} poin)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tipe <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-green-50">
                            <input type="radio" name="tipe" value="tambah" checked class="accent-green-600">
                            <span class="text-sm text-green-700 font-medium">+ Tambah</span>
                        </label>
                        <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:bg-red-50">
                            <input type="radio" name="tipe" value="kurang" class="accent-red-600">
                            <span class="text-sm text-red-700 font-medium">– Kurangi</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Jumlah poin <span class="text-red-400">*</span></label>
                    <input type="number" name="poin" required min="1"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="50">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Keterangan <span class="text-red-400">*</span></label>
                    <input type="text" name="keterangan" required
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                           placeholder="Bonus event, koreksi poin, dll">
                </div>
                <button type="submit"
                        class="w-full bg-brand text-white text-sm font-medium py-2.5 rounded-lg hover:bg-brand-hover">
                    Simpan perubahan poin
                </button>
            </form>
        </div>

        {{-- Tabel poin pelanggan --}}
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden mt-4">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Saldo poin pelanggan</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Nama</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pelanggans as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-gray-900 text-xs">{{ $p->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $p->no_hp_display }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <span class="font-semibold text-blue-700 text-sm">{{ number_format($p->poin, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-xs text-gray-400">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat poin --}}
    <div class="col-span-2">
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Riwayat 20 transaksi poin terakhir</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Keterangan</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($histories as $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-xs text-gray-400">
                            {{ $h->created_at->isoFormat('D MMM Y, HH:mm') }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $h->pelanggan->nama }}</td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $h->keterangan }}</td>
                        <td class="px-5 py-3 text-right font-semibold
                                   {{ $h->tipe === 'tambah' ? 'text-green-700' : 'text-red-600' }}">
                            {{ $h->tipe === 'tambah' ? '+' : '-' }}{{ number_format($h->poin, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">
                            Belum ada riwayat transaksi poin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection
