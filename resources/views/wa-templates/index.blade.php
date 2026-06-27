@extends('layouts.app')
@section('title', 'Template WA')

@section('content')
<div class="max-w-3xl">
    <div class="mb-5">
        <p class="text-xs text-gray-500">Edit isi pesan WhatsApp yang dikirim otomatis ke pelanggan. Gunakan placeholder <code class="bg-gray-100 px-1 rounded">{nama_placeholder}</code> untuk data dinamis.</p>
    </div>

    <div class="space-y-3">
        @foreach($templates as $tmpl)
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $tmpl->nama }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $tmpl->kode }}</p>
            </div>
            <a href="{{ route('wa-templates.edit', $tmpl) }}"
               class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                Edit →
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
