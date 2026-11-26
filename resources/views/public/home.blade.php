@extends('layouts.public')

@section('page_id', 'public-home')
@section('title', 'Andara Motor | Katalog Motor Bekas Siap Pakai')
@section('meta_description', 'Showroom motor bekas Andara Motor. Cek stok tersedia, detail surat, pajak, dan kilometer secara transparan.')

@section('content')
    {{-- 1. Full-Bleed Hero Showroom Banner --}}
    <section class="relative overflow-hidden bg-ink text-white">
        <img
            src="{{ asset('images/motorcycles/Andara Scooter Dealership at Dusk.png') }}"
            alt="Showroom Andara Motor dengan unit skuter merah saat senja"
            width="2138"
            height="735"
            fetchpriority="high"
            decoding="async"
            class="absolute inset-0 h-full w-full object-cover object-[74%_center] sm:object-[68%_center] lg:object-right"
        >
        <div
            aria-hidden="true"
            class="absolute inset-0 bg-gradient-to-r from-[#0c0e13]/92 via-[#0c0e13]/68 to-[#0c0e13]/15"
        ></div>
        <div
            aria-hidden="true"
            class="absolute inset-0 bg-gradient-to-t from-[#0c0e13]/92 via-[#0c0e13]/20 to-transparent"
        ></div>

        <div class="relative z-10 mx-auto flex min-h-[540px] max-w-7xl flex-col justify-between px-4 pb-8 pt-12 sm:px-6 lg:min-h-[calc(100svh-112px)] lg:max-h-[740px] lg:px-8 lg:pb-9 lg:pt-16">
            <div class="hero-enter my-auto max-w-xl space-y-4 py-6 lg:py-8">
                <h1 class="text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl">
                    Motor Bekas Pilihan.
                    <span class="block text-red-500">Surat Lengkap &amp; Jelas.</span>
                </h1>

                <p class="max-w-md text-base leading-relaxed text-slate-200">
                    Cek tahun, kilometer, pajak, dan kondisi mesin sebelum datang ke showroom.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('catalog.index') }}" class="btn-primary">
                        Lihat Katalog Motor
                    </a>
                    <a
                        data-store-wa-link
                        href="https://wa.me/6282298213826"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 bg-black/30 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/15"
                    >
                        Tanya via WhatsApp
                    </a>
                </div>
            </div>

            <div class="grid gap-4 border-t border-white/15 pt-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M9 12.75 11.25 15 15 9.75" />
                        <path d="M12 3 4 7v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V7l-8-4Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-white">Surat Terverifikasi</p>
                        <p class="text-xs text-slate-200">Status BPKB, STNK &amp; faktur tercatat per unit.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-white">Stok Real-Time</p>
                        <p class="text-xs text-slate-200">Unit terjual langsung hilang dari katalog aktif.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-5.3 5.3a1.5 1.5 0 0 0 2.1 2.1l5.3-5.3a4 4 0 0 0 5.4-5.4l-2.3 2.3-2.1-2.1 2.3-2.3Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-white">Info Mesin Terbuka</p>
                        <p class="text-xs text-slate-200">Kondisi fisik, kilometer &amp; garansi dicantumkan jelas.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 11.5a8.4 8.4 0 0 1-12.5 7.3L3 20l1.2-5.1A8.4 8.4 0 1 1 21 11.5Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-white">Chat Langsung Showroom</p>
                        <p class="text-xs text-slate-200">Tanya unit &amp; jadwal cek langsung lewat WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Featured & Latest Motors Grid --}}
    <section aria-labelledby="unit-pilihan-heading" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
            <h2 id="unit-pilihan-heading" class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                Unit Pilihan <span data-store-name class="text-brand">Andara Motor</span>
            </h2>
            <a href="{{ route('catalog.index') }}" class="text-sm font-bold text-brand hover:underline">
                Lihat Semua Motor
            </a>
        </div>

        <div id="home-motors-grid" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="col-span-full py-12 text-center text-sm font-medium text-muted" role="status">
                Memuat unit motor pilihan...
            </div>
        </div>
    </section>

    {{-- 3. Why Choose Andara Motor (clean horizontal strip matching reference) --}}
    <section aria-labelledby="kenapa-andara-heading" class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <h2 id="kenapa-andara-heading" class="text-xl font-extrabold tracking-tight text-ink sm:text-2xl">
            Kenapa harus <span data-store-name class="text-brand">Andara Motor</span>?
        </h2>

        <div class="mt-5 grid grid-cols-1 divide-y divide-line rounded-lg border border-line bg-white sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-5 lg:divide-x lg:divide-line">
            <div class="flex items-center gap-3.5 px-5 py-5">
                <svg class="h-8 w-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 12.75 11.25 15 15 9.75" />
                    <path d="M12 3 4 7v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V7l-8-4Z" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-ink">Dokumen Lengkap</p>
                    <p class="mt-0.5 text-xs text-muted">BPKB, STNK &amp; faktur terverifikasi</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-5 py-5">
                <svg class="h-8 w-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="17" rx="2" />
                    <path d="M8 2v4M16 2v4M3 10h18" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-ink">Pajak Transparan</p>
                    <p class="mt-0.5 text-xs text-muted">Status pajak ditulis apa adanya</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-5 py-5">
                <svg class="h-8 w-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-ink">Harga Jelas</p>
                    <p class="mt-0.5 text-xs text-muted">Tanpa biaya tersembunyi, siap nego</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-5 py-5">
                <svg class="h-8 w-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-5.3 5.3a1.5 1.5 0 0 0 2.1 2.1l5.3-5.3a4 4 0 0 0 5.4-5.4l-2.3 2.3-2.1-2.1 2.3-2.3Z" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-ink">Garansi Mesin</p>
                    <p class="mt-0.5 text-xs text-muted">Jaminan mesin sesuai keterangan unit</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 px-5 py-5 sm:col-span-2 lg:col-span-1">
                <svg class="h-8 w-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-ink">Cek Fisik di Lokasi</p>
                    <p class="mt-0.5 text-xs text-muted">Inspeksi &amp; test ride di showroom</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Featured Brand Logos Continuous Left-to-Right Marquee (#merek) --}}
    @php
        $brandLogos = [
            ['name' => 'Honda', 'file' => 'honda-seeklogo.png', 'w' => 2000, 'h' => 1605, 'imgClass' => 'max-h-11 max-w-[110px]'],
            ['name' => 'Yamaha', 'file' => 'yamaha-seeklogo.png', 'w' => 2000, 'h' => 425, 'imgClass' => 'max-h-8 max-w-[140px]'],
            ['name' => 'Suzuki', 'file' => 'suzuki-seeklogo.png', 'w' => 2000, 'h' => 384, 'imgClass' => 'max-h-8 max-w-[140px]'],
            ['name' => 'Kawasaki', 'file' => 'kawasaki-seeklogo.png', 'w' => 2000, 'h' => 212, 'imgClass' => 'max-h-6 max-w-[148px]'],
            ['name' => 'TVS', 'file' => 'tvs-seeklogo.png', 'w' => 2000, 'h' => 389, 'imgClass' => 'max-h-8 max-w-[136px]'],
        ];
    @endphp
    <section id="merek" aria-labelledby="merek-heading" class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="merek-heading" class="text-xl font-extrabold tracking-tight text-ink sm:text-2xl">
                Merek Unggulan</span>
            </h2>
            <button
                type="button"
                id="brand-marquee-toggle"
                aria-pressed="false"
                aria-label="Jeda animasi logo merek"
                class="inline-flex min-h-9 items-center rounded border border-line bg-white px-3 text-xs font-semibold text-muted hover:border-ink hover:text-ink"
            >
                Jeda
            </button>
        </div>

        <div id="brand-marquee" class="brand-marquee">
            <div class="brand-marquee__track">
                {{-- Group 1 (interactive & accessible) --}}
                <div class="brand-marquee__group">
                    @foreach ($brandLogos as $brand)
                        <a
                            href="{{ route('catalog.index', ['merk' => $brand['name']]) }}"
                            aria-label="Lihat katalog motor {{ $brand['name'] }}"
                            class="brand-logo-card"
                        >
                            <img
                                src="{{ asset('images/logo-merek/'.$brand['file']) }}"
                                alt="{{ $brand['name'] }}"
                                width="{{ $brand['w'] }}"
                                height="{{ $brand['h'] }}"
                                decoding="async"
                                class="{{ $brand['imgClass'] }} h-auto w-auto object-contain"
                            >
                        </a>
                    @endforeach
                </div>

                {{-- Group 2 (seamless loop filler) --}}
                <div class="brand-marquee__group" aria-hidden="true">
                    @foreach ($brandLogos as $brand)
                        <a
                            href="{{ route('catalog.index', ['merk' => $brand['name']]) }}"
                            tabindex="-1"
                            class="brand-logo-card"
                        >
                            <img
                                src="{{ asset('images/logo-merek/'.$brand['file']) }}"
                                alt=""
                                width="{{ $brand['w'] }}"
                                height="{{ $brand['h'] }}"
                                decoding="async"
                                class="{{ $brand['imgClass'] }} h-auto w-auto object-contain"
                            >
                        </a>
                    @endforeach
                </div>

                {{-- Group 3 & 4 (ensures zero gap on wide displays for smooth left-to-right loop) --}}
                <div class="brand-marquee__group" aria-hidden="true">
                    @foreach ($brandLogos as $brand)
                        <a
                            href="{{ route('catalog.index', ['merk' => $brand['name']]) }}"
                            tabindex="-1"
                            class="brand-logo-card"
                        >
                            <img
                                src="{{ asset('images/logo-merek/'.$brand['file']) }}"
                                alt=""
                                width="{{ $brand['w'] }}"
                                height="{{ $brand['h'] }}"
                                decoding="async"
                                class="{{ $brand['imgClass'] }} h-auto w-auto object-contain"
                            >
                        </a>
                    @endforeach
                </div>

                <div class="brand-marquee__group" aria-hidden="true">
                    @foreach ($brandLogos as $brand)
                        <a
                            href="{{ route('catalog.index', ['merk' => $brand['name']]) }}"
                            tabindex="-1"
                            class="brand-logo-card"
                        >
                            <img
                                src="{{ asset('images/logo-merek/'.$brand['file']) }}"
                                alt=""
                                width="{{ $brand['w'] }}"
                                height="{{ $brand['h'] }}"
                                decoding="async"
                                class="{{ $brand['imgClass'] }} h-auto w-auto object-contain"
                            >
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
