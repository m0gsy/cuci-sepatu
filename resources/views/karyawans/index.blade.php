@extends('layouts.app')
@section('title', 'Manajemen karyawan')

@section('header-actions')
<button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
        class="bg-brand text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-brand-hover transition-colors">
    + Tambah karyawan
</button>
@endsection

@section('content')
<div class="space-y-5 max-w-4xl">

@forelse($karyawans as $k)
<div class="bg-white border border-gray-100 rounded-xl p-5 {{ !$k->aktif ? 'opacity-60' : '' }}"
     x-data="{ edit: false, resetPw: false }">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold text-gray-600 shrink-0">
                {{ $k->inisial }}
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                    <p class="text-sm font-semibold text-gray-900">{{ $k->name }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $k->role_badge }}">
                        {{ $k->role_label }}
                    </span>
                    @if(!$k->aktif)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 ring-1 ring-red-200">Nonaktif</span>
                    @endif
                    @if($k->id === auth()->id())
                    <span class="text-xs text-gray-400">(Anda)</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $k->email }}</p>
            </div>
        </div>

        <div class="flex items-center gap-6 text-center">
            <div><p class="text-lg font-semibold text-gray-900">{{ $k->order_hari_ini }}</p><p class="text-xs text-gray-400">hari ini</p></div>
            <div><p class="text-lg font-semibold text-gray-900">{{ $k->order_bulan_ini }}</p><p class="text-xs text-gray-400">bulan ini</p></div>
            @if($k->last_login)
            <div><p class="text-xs font-medium text-gray-700">{{ $k->last_login->diffForHumans() }}</p><p class="text-xs text-gray-400">login terakhir</p></div>
            @endif
        </div>

        <div class="flex items-center gap-2" x-show="!edit && !resetPw">
            <a href="{{ route('karyawans.show', $k) }}" class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">Detail</a>
            @if($k->id !== auth()->id())
            <button @click="edit = true" class="text-xs text-gray-400 hover:text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg">Edit</button>
            <form method="POST" action="{{ route('karyawans.toggle', $k) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs border px-3 py-1.5 rounded-lg transition-colors {{ $k->aktif ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                    {{ $k->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <div x-show="edit" class="mt-4 pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('karyawans.update', $k) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div><label class="block text-xs text-gray-500 mb-1">Nama</label><input type="text" name="name" value="{{ $k->name }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
                <div><label class="block text-xs text-gray-500 mb-1">Email</label><input type="email" name="email" value="{{ $k->email }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
                <div><label class="block text-xs text-gray-500 mb-1">Role</label>
                    <select name="role" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="owner"   {{ $k->role === 'owner'   ? 'selected' : '' }}>Owner</option>
                        <option value="admin"   {{ $k->role === 'admin'   ? 'selected' : '' }}>Admin</option>
                        <option value="cleaner" {{ $k->role === 'cleaner' ? 'selected' : '' }}>Cleaner</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Simpan</button>
                <button type="button" @click="edit = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="button" @click="edit = false; resetPw = true" class="px-4 py-2 text-sm text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-50 ml-auto">Reset password</button>
            </div>
        </form>
    </div>

    <div x-show="resetPw" class="mt-4 pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('karyawans.password', $k) }}">
            @csrf @method('PATCH')
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div><label class="block text-xs text-gray-500 mb-1">Password baru</label><input type="password" name="password" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Min. 8 karakter" required></div>
                <div><label class="block text-xs text-gray-500 mb-1">Konfirmasi</label><input type="password" name="password_confirmation" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm bg-amber-700 text-white rounded-lg hover:bg-amber-800">Reset password</button>
                <button type="button" @click="resetPw = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
            </div>
        </form>
    </div>

</div>
@empty
<div class="bg-white border border-gray-100 rounded-xl p-12 text-center">
    <p class="text-sm text-gray-400">Belum ada karyawan.</p>
</div>
@endforelse
</div>

{{-- ── HAK AKSES PER ROLE ───────────────────────────────────────── --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hak akses per role</p>
        <p class="text-xs text-gray-400">Owner selalu punya semua akses</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <form method="POST" action="{{ route('karyawans.permissions') }}">
            @csrf
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 w-1/2">Menu / Fitur</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 w-1/4">Admin</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 w-1/4">Cleaner</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($allPermissions as $perm)
                    <tr class="border-b border-gray-50">
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $perm['label'] }}</td>
                        <td class="px-5 py-3 text-center">
                            <input type="checkbox"
                                   name="permissions[admin][]"
                                   value="{{ $perm['key'] }}"
                                   {{ in_array($perm['key'], $adminPermissions) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-brand cursor-pointer">
                        </td>
                        <td class="px-5 py-3 text-center">
                            <input type="checkbox"
                                   name="permissions[cleaner][]"
                                   value="{{ $perm['key'] }}"
                                   {{ in_array($perm['key'], $cleanerPermissions) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-brand cursor-pointer">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="px-5 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover transition-colors">
                    Simpan hak akses
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-tambah" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-100 p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Tambah karyawan</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
        </div>
        <form method="POST" action="{{ route('karyawans.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div><label class="block text-xs text-gray-500 mb-1.5">Nama lengkap</label><input type="text" name="name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
                <div><label class="block text-xs text-gray-500 mb-1.5">Role</label>
                    <select name="role" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                        <option value="cleaner">Cleaner</option>
                    </select>
                </div>
                <div><label class="block text-xs text-gray-500 mb-1.5">Email</label><input type="email" name="email" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
                <div><label class="block text-xs text-gray-500 mb-1.5">No. HP</label><input type="text" name="no_hp" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-xs text-gray-500 mb-1.5">Password</label><input type="password" name="password" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Min. 8 karakter" required></div>
                <div><label class="block text-xs text-gray-500 mb-1.5">Konfirmasi</label><input type="password" name="password_confirmation" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand" required></div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="flex-1 py-2.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="flex-1 py-2.5 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover">Buat akun</button>
            </div>
        </form>
    </div>
</div>

@endsection
