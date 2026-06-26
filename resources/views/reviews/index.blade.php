@extends('layouts.app')
@section('title', 'Ulasan Pelanggan')

@section('content')
<div class="space-y-5">

    {{-- Stat ringkas --}}
    @php
        $totalUlasan  = \App\Models\Review::count();
        $rataRata     = $totalUlasan > 0 ? number_format(\App\Models\Review::avg('rating'), 1) : '—';
        $bintangLima  = \App\Models\Review::where('rating', 5)->count();
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 mb-1">Total ulasan</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalUlasan }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 mb-1">Rating rata-rata</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $rataRata }} <span class="text-yellow-400 text-lg">★</span></p>
        </div>
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <p class="text-xs text-gray-400 mb-1">Rating bintang 5</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $bintangLima }}</p>
        </div>
    </div>

    {{-- Daftar ulasan --}}
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Semua Ulasan</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($reviews as $review)
            <div class="px-5 py-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-yellow-400 text-sm">{{ $review->bintang }}</span>
                            <span class="text-xs font-medium text-gray-900">{{ $review->pelanggan?->nama ?? 'Anonim' }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-1">
                            Order <a href="{{ route('orders.show', $review->order_id) }}"
                                     class="text-blue-600 hover:underline font-mono">
                                {{ $review->order?->no_order }}
                            </a>
                            — {{ $review->order?->layanan?->nama }}
                        </p>
                        @if($review->ulasan)
                        <p class="text-sm text-gray-700 mt-2 bg-gray-50 rounded-lg px-3 py-2">
                            "{{ $review->ulasan }}"
                        </p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 shrink-0 ml-4">
                        {{ $review->created_at->isoFormat('D MMM Y') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-gray-400">Belum ada ulasan dari pelanggan.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{ $reviews->links() }}

</div>
@endsection
