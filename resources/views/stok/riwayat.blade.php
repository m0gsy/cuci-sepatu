@extends('layouts.app')
@section('title', 'Riwayat stok — ' . $stok->nama)

@section('header-actions')
    <a href="{{ route('stok.index') }}" class="text-xs text-gray-400 hover:text-gray-700">← Kembali</a>
@endsection

@section('content')
<div class="max-w-2xl space-y-5">

    <div class="bg-white border border-gray-100 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ $stok->nama }}</p>
                <p class="text-sm text-gray-500">Satuan: {{ $stok->satuan }} · Min: {{ $stok->stok_minimum }}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stok->stok_saat_ini, 1) }}</p>
                <p class="text-xs text-gray-400">{{ $stok->satuan }} saat ini</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-900">Riwayat mutasi</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[520px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 whitespace-nowrap">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Tipe</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Jumlah</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Setelah</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Oleh</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Keterangan</th>
                </tr>
            </thead>
            <tbody>
            @forelse($mutasis as $m)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $m->created_at->isoFormat('D MMM Y, HH:mm') }}</td>
                    <td class="px-5 py-3">
                        @php
                            $tipeBadge = match($m->tipe) {
                                'masuk'       => 'bg-green-50 text-green-700',
                                'keluar'      => 'bg-red-50 text-red-700',
                                'penyesuaian' => 'bg-blue-50 text-blue-700',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tipeBadge }}">
                            {{ ucfirst($m->tipe) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-right font-medium
                               {{ $m->tipe === 'masuk' ? 'text-green-700' : ($m->tipe === 'keluar' ? 'text-red-700' : 'text-gray-900') }}">
                        {{ $m->tipe === 'masuk' ? '+' : ($m->tipe === 'keluar' ? '-' : '') }}{{ number_format($m->jumlah, 1) }}
                    </td>
                    <td class="px-5 py-3 text-sm text-right text-gray-900">{{ number_format($m->stok_sesudah, 1) }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $m->user->name }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $m->keterangan ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada riwayat mutasi.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        @if($mutasis->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $mutasis->links() }}</div>
        @endif
    </div>
</div>
@endsection
