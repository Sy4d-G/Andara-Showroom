@extends('layouts.public')

@section('page_id', 'public-catalog')
@section('title', 'Katalog Motor Bekas | Andara Motor')
@section('meta_description', 'Cari dan filter motor bekas tersedia di Andara Motor berdasarkan merk, tipe motor, harga, dan tahun.')

@section('content')
    <section class="border-b border-line bg-surface">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                Katalog Motor Bekas <span data-store-name>Andara Motor</span>
            </h1>
            <p id="catalog-summary" class="mt-1.5 text-sm text-muted">
                Memuat stok motor tersedia...
            </p>

            <search class="mt-6 block">
                <form id="catalog-filter-form" method="GET" action="{{ route('catalog.index') }}" class="rounded-lg border border-line bg-white p-4">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                        <div class="lg:col-span-2">
                            <label for="filter-search" class="block text-xs font-semibold text-muted">Kata Kunci</label>
                            <input
                                id="filter-search"
                                name="search"
                                type="search"
                                placeholder="Nama motor, merk, atau kondisi"
                                class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink"
                            >
                        </div>

                        <div>
                            <label for="filter-merk" class="block text-xs font-semibold text-muted">Merk</label>
                            <select id="filter-merk" name="merk" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                                <option value="">Semua Merk</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter-tipe" class="block text-xs font-semibold text-muted">Tipe Motor</label>
                            <select id="filter-tipe" name="tipe_motor" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                                <option value="">Semua Tipe</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter-status" class="block text-xs font-semibold text-muted">Status</label>
                            <select id="filter-status" name="status" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                                <option value="tersedia">Tersedia</option>
                                <option value="booking">Booking (DP)</option>
                                <option value="all">Semua Status</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter-sort" class="block text-xs font-semibold text-muted">Urutkan</label>
                            <select id="filter-sort" name="sort" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                                <option value="created_at:desc">Terbaru</option>
                                <option value="harga_jual:asc">Harga Termurah</option>
                                <option value="harga_jual:desc">Harga Termahal</option>
                                <option value="tahun:desc">Tahun Terbaru</option>
                                <option value="jarak_tempuh:asc">Kilometer Terendah</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 border-t border-line pt-3 sm:grid-cols-2 lg:grid-cols-6">
                        <div>
                            <label for="filter-harga-min" class="block text-xs font-semibold text-muted">Harga Min (Rp)</label>
                            <input id="filter-harga-min" name="harga_min" type="number" inputmode="numeric" min="0" step="500000" placeholder="10000000" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums">
                        </div>

                        <div>
                            <label for="filter-harga-max" class="block text-xs font-semibold text-muted">Harga Maks (Rp)</label>
                            <input id="filter-harga-max" name="harga_max" type="number" inputmode="numeric" min="0" step="500000" placeholder="50000000" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums">
                        </div>

                        <div>
                            <label for="filter-tahun-min" class="block text-xs font-semibold text-muted">Tahun Min</label>
                            <input id="filter-tahun-min" name="tahun_min" type="number" inputmode="numeric" min="1990" max="{{ date('Y') + 1 }}" placeholder="2018" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums">
                        </div>

                        <div>
                            <label for="filter-tahun-max" class="block text-xs font-semibold text-muted">Tahun Maks</label>
                            <input id="filter-tahun-max" name="tahun_max" type="number" inputmode="numeric" min="1990" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums">
                        </div>

                        <div class="flex items-end gap-2 sm:col-span-2">
                            <button type="submit" class="btn-primary flex-1 text-sm">Terapkan Filter</button>
                            <a id="catalog-reset-btn" href="{{ route('catalog.index') }}" hidden class="btn-secondary text-sm">Reset Filter</a>
                        </div>
                    </div>
                </form>
            </search>
        </div>
    </section>

    <section aria-label="Daftar Unit Katalog" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div id="catalog-motors-grid" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="col-span-full py-12 text-center text-sm font-medium text-muted" role="status">
                Memuat daftar unit motor...
            </div>
        </div>

        <div id="catalog-pagination" class="mt-10"></div>
    </section>
@endsection
