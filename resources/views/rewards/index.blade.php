@extends('layouts.app')
@section('title', 'Reward & Poin')

@section('header-actions')
<div class="flex gap-2">
    <a href="{{ route('rewards.poin') }}"
       class="text-xs border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50">
        Kelola poin pelanggan
    </a>
    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
            class="text-xs bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-hover">
        + Tambah reward
    </button>
</div>
@endsection

@section('content')
<div class="space-y-5">

    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-900">Daftar reward</p>
            <p class="text-xs text-gray-400 mt-0.5">Pelanggan dapat menukar poin dengan reward yang tersedia</p>
        </div>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Nama reward</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500">Poin dibutuhkan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500">Deskripsi</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($rewards as $reward)
                <tr class="hover:bg-gray-50" x-data="{ editOpen: false }">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $reward->nama }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-semibold text-blue-700">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400 ml-0.5">poin</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $reward->deskripsi ?? '-' }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $reward->aktif ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500' }}">
                            {{ $reward->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2 justify-end" x-show="!editOpen">
                            <button @click="editOpen = true"
                                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-2 py-1 rounded-lg">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('rewards.toggle', $reward) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs border px-2 py-1 rounded-lg {{ $reward->aktif ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                                    {{ $reward->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('rewards.destroy', $reward) }}"
                                  onsubmit="return confirm('Hapus reward ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs border border-red-200 text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg">
                                    Hapus
                                </button>
                            </form>
                        </div>

                        {{-- Form edit inline --}}
                        <form method="POST" action="{{ route('rewards.update', $reward) }}"
                              x-show="editOpen" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="nama" value="{{ $reward->nama }}" required
                                   class="rounded-lg border border-gray-200 px-2 py-1 text-xs w-32 focus:outline-none focus:ring-2 focus:ring-brand">
                            <input type="number" name="poin_dibutuhkan" value="{{ $reward->poin_dibutuhkan }}" required min="1"
                                   class="rounded-lg border border-gray-200 px-2 py-1 text-xs w-24 text-right focus:outline-none focus:ring-2 focus:ring-brand">
                            <input type="text" name="deskripsi" value="{{ $reward->deskripsi }}"
                                   class="rounded-lg border border-gray-200 px-2 py-1 text-xs w-40 focus:outline-none focus:ring-2 focus:ring-brand"
                                   placeholder="Deskripsi">
                            <button type="submit"
                                    class="text-xs bg-brand text-white px-2.5 py-1 rounded-lg">Simpan</button>
                            <button type="button" @click="editOpen = false"
                                    class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-2.5 py-1 rounded-lg">Batal</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">
                        Belum ada reward. Tambahkan reward untuk pelanggan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal tambah reward --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900">Tambah reward baru</h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        <form method="POST" action="{{ route('rewards.store') }}" class="px-6 py-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1.5">Nama reward <span class="text-red-400">*</span></label>
                <input type="text" name="nama" required autofocus
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                       placeholder="Cuci gratis, Diskon 20%, dll">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1.5">Poin dibutuhkan <span class="text-red-400">*</span></label>
                <input type="number" name="poin_dibutuhkan" required min="1"
                       class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                       placeholder="100">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="2"
                          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
                          placeholder="Keterangan singkat reward ini (opsional)"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-brand text-white text-sm font-medium py-2.5 rounded-lg hover:bg-brand-hover">
                    Simpan reward
                </button>
                <button type="button"
                        onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                        class="px-5 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
