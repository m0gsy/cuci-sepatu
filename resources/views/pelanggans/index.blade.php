@extends('layouts.app')
@section('title', 'Pelanggan')

@section('header-actions')
<button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
        class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
    + Tambah pelanggan
</button>
@endsection

@section('content')
<div class="space-y-5">

<form method="GET" class="flex gap-3">
    <input type="text" name="cari" value="{{ request('cari') }}"
           placeholder="Cari nama atau nomor HP..."
           class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
    <button type="submit" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cari</button>
    @if(request('cari'))
    <a href="{{ route('pelanggans.index') }}"
       class="px-4 py-2 text-sm text-gray-400 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
    @endif
</form>

<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Pelanggan</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Order</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Total belanja</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Poin</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Order terakhir</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($pelanggans as $p)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ $p->nama }}</p>
                    <p class="text-xs text-gray-400">{{ $p->no_hp }}</p>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-sm font-medium">{{ $p->orders_count }}</span>
                </td>
                <td class="px-5 py-3 text-right">
                    <span class="text-sm font-semibold text-gray-900">
                        Rp {{ number_format($p->total_belanja ?? 0, 0, ',', '.') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                 {{ $p->poin > 0 ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-200' : 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' }}">
                        {{ $p->poin }} poin
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500">
                    @if($p->latestOrder)
                        {{ $p->latestOrder->created_at->isoFormat('D MMM Y') }}
                        <span class="block text-gray-400">{{ $p->latestOrder->layanan?->nama }}</span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('pelanggans.show', $p) }}"
                           class="text-xs text-gray-400 hover:text-gray-900 transition-colors">Riwayat →</a>
                        <a href="{{ $p->wa_link }}" target="_blank"
                           class="text-xs text-green-600 hover:text-green-800 transition-colors">WA</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">Belum ada pelanggan.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($pelanggans->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $pelanggans->links() }}</div>
    @endif
</div>

</div>

{{-- Modal tambah pelanggan --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-100 p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold">Tambah pelanggan</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700 text-lg">✕</button>
        </div>
        <form method="POST" action="{{ route('pelanggans.store') }}">
            @csrf
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Nama</label>
                    <input type="text" name="nama" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">No. HP</label>
                    <input type="text" name="no_hp" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="0812-xxxx-xxxx" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Alamat</label>
                    <input type="text" name="alamat" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
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
