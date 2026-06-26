@extends('layouts.app')
@section('title', 'Profil saya')

@section('content')
<div class="max-w-lg space-y-5">

    {{-- Info akun --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Informasi akun</h2>

        @if(session('status') === 'profile-updated')
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-xs px-3 py-2 rounded-lg">
            Profil berhasil diperbarui.
        </div>
        @endif

        <form method="POST" action="{{ route('profil.update') }}">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('name') border-red-400 @enderror">
                    @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('email') border-red-400 @enderror">
                    @error('email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover transition-colors">
                        Simpan perubahan
                    </button>
                    <span class="text-xs text-gray-400 capitalize">Role: {{ $user->role_label }}</span>
                </div>
            </div>
        </form>
    </div>

    {{-- Ganti password --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Ganti password</h2>

        @if(session('status') === 'password-updated')
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-xs px-3 py-2 rounded-lg">
            Password berhasil diperbarui.
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password sekarang</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('current_password', 'updatePassword') border-red-400 @enderror">
                    @error('current_password', 'updatePassword')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password baru</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('password', 'updatePassword') border-red-400 @enderror">
                    @error('password', 'updatePassword')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi password baru</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-brand text-white rounded-lg hover:bg-brand-hover transition-colors">
                    Ganti password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
