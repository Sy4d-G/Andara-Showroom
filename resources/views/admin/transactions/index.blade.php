@extends('layouts.admin')

@section('page_id', 'admin-transactions-index')
@section('title', 'Transaksi Penjualan')
@section('page_title', 'Transaksi Penjualan')

@section('header_actions')
    <a href="{{ route('admin.transactions.create') }}" class="btn-primary text-sm">
        Catat Transaksi
    </a>
@endsection

@section('content')
    <div id="tx-feedback" role="status" hidden></div>

    <section aria-label="Filter riwayat transaksi" class="mb-6 rounded-lg border border-line bg-white p-4">
        <search>
            <form id="admin-tx-filter" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label for="tx-filter-search" class="block text-xs font-semibold text-muted">Cari Kode / Pembeli / Unit Motor</label>
                    <input
                        id="tx-filter-search"
                        name="search"
                        type="search"
                        placeholder="TRX-..., nama pembeli, atau Vario"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink"
                    >
                </div>

                <div>
                    <label for="tx-filter-start" class="block text-xs font-semibold text-muted">Tanggal Mulai</label>
                    <input
                        id="tx-filter-start"
                        name="tanggal_mulai"
                        type="date"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums"
                    >
                </div>

                <div>
                    <label for="tx-filter-end" class="block text-xs font-semibold text-muted">Tanggal Selesai</label>
                    <input
                        id="tx-filter-end"
                        name="tanggal_selesai"
                        type="date"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums"
                    >
                </div>

                <div>
                    <label for="tx-filter-method" class="block text-xs font-semibold text-muted">Metode Pembayaran</label>
                    <select id="tx-filter-method" name="metode_pembayaran" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                        <option value="">Semua Metode</option>
                        <option value="Cash">Cash (Tunai)</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Kredit">Kredit / Leasing</option>
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" class="btn-dark text-sm">Terapkan Filter</button>
                    <button id="tx-filter-reset" type="button" class="btn-secondary text-sm">Reset</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn-secondary ml-auto text-sm">
                        Buka Laporan Keuangan
                    </a>
                </div>
            </form>
        </search>
    </section>

    <section aria-labelledby="tx-list-heading" class="rounded-lg border border-line bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
            <div>
                <h2 id="tx-list-heading" class="text-base font-bold text-ink">Riwayat Transaksi Penjualan</h2>
                <p class="text-xs text-muted">Setiap transaksi otomatis mengubah status unit menjadi terjual dan menghitung keuntungan bersih.</p>
            </div>
            <span id="tx-total-count" class="text-xs font-semibold text-muted tabular-nums">...</span>
        </div>

        <div id="admin-tx-table-container">
            <div class="px-6 py-10 text-center text-sm text-muted" role="status">Memuat daftar transaksi penjualan...</div>
        </div>
    </section>
@endsection