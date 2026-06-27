@extends('layouts.app')
@section('title', 'Edit Template: ' . $template->nama)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white border border-gray-200 rounded-xl p-6">

        <form method="POST" action="{{ route('wa-templates.update', $template) }}">
            @csrf @method('PATCH')

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-2">Isi Pesan</label>
                <textarea name="template" rows="14"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 font-mono focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand resize-y"
                >{{ old('template', $template->template) }}</textarea>
                @error('template') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Placeholder reference --}}
            @php
                $placeholders = [
                    'order_masuk'   => ['{nama_pelanggan}','{no_order}','{layanan}','{lokasi}','{jumlah_pasang}','{total}','{metode_bayar}','{estimasi_selesai}','{link_status}'],
                    'mulai_dicuci'  => ['{nama_pelanggan}','{no_order}','{layanan}','{lokasi}','{link_status}'],
                    'order_selesai' => ['{nama_pelanggan}','{no_order}','{layanan}','{lokasi}','{total}','{status_bayar}','{poin}','{link_status}'],
                    'invoice'       => ['{nama_pelanggan}','{no_order}','{tanggal}','{layanan}','{lokasi}','{jenis_sepatu}','{jumlah_pasang}','{harga_satuan}','{total}','{metode_bayar}','{status_bayar}'],
                ];
                $vars = $placeholders[$template->kode] ?? [];
            @endphp
            @if($vars)
            <div class="mb-5 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-semibold text-gray-600 mb-2">Placeholder tersedia:</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($vars as $v)
                    <code class="text-xs bg-white border border-gray-200 px-2 py-0.5 rounded cursor-pointer hover:bg-brand hover:text-white hover:border-brand transition-colors"
                          onclick="insertPlaceholder('{{ $v }}')"
                          title="Klik untuk sisipkan">{{ $v }}</code>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-2">⚠ Baris dengan placeholder opsional yang kosong ({lokasi}, {poin}) akan otomatis dihapus saat pengiriman.</p>
            </div>
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background:#1a3a2a;" onmouseover="this.style.background='#243f30'" onmouseout="this.style.background='#1a3a2a'">
                    Simpan
                </button>
                <a href="{{ route('wa-templates.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Batal</a>
                <form method="POST" action="{{ route('wa-templates.reset', $template) }}" class="ml-auto" onsubmit="return confirm('Reset ke template default?')">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">Reset ke default</button>
                </form>
            </div>
        </form>
    </div>
</div>

<script>
function insertPlaceholder(text) {
    const ta = document.querySelector('textarea[name=template]');
    const start = ta.selectionStart, end = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + text + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + text.length;
    ta.focus();
}
</script>
@endsection
