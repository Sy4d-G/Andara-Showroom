@extends('layouts.public')

@section('page_id', 'public-detail')
@section('title', 'Detail Unit Motor | Andara Motor')
@section('body_class', 'pb-20 md:pb-0')

@section('content')
    <div id="detail-root" data-motor-id="{{ $motorId }}" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="py-16 text-center text-sm font-medium text-muted" role="status">
            Memuat detail spesifikasi unit motor...
        </div>
    </div>

    {{-- Sticky Mobile WhatsApp CTA Bar --}}
    <div id="mobile-wa-bar" hidden class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white px-4 py-3 md:hidden">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-muted">Harga Jual</p>
                <p id="mobile-wa-price" class="text-base font-extrabold text-ink tabular-nums"></p>
            </div>
            <a id="mobile-wa-link" href="#" target="_blank" rel="noopener noreferrer" class="btn-wa flex-1 text-sm">
                Tanya via WhatsApp
            </a>
        </div>
    </div>
@endsection
