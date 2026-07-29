@extends('layouts.app')

@section('title', 'Pesan kontak')

@section('content')
<div class="max-w-5xl space-y-4">
    @forelse($messages as $message)
        <article class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-gray-900">{{ $message->subject ?: 'Tanpa subjek' }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ $message->name }} &middot;
                        <a href="mailto:{{ $message->email }}" class="text-emerald-700 hover:underline">{{ $message->email }}</a>
                    </p>
                </div>
                <time class="text-xs text-gray-400" datetime="{{ $message->created_at->toAtomString() }}">
                    {{ $message->created_at->isoFormat('D MMM Y, HH:mm') }}
                </time>
            </div>
            <p class="mt-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
        </article>
    @empty
        <div class="bg-white border border-gray-100 rounded-xl p-8 text-center text-sm text-gray-500">
            Belum ada pesan kontak.
        </div>
    @endforelse

    {{ $messages->links() }}
</div>
@endsection
