@extends('layouts.admin')

@section('page_id', 'admin-reports-index')
@section('title', 'Laporan Keuangan')
@section('page_title', 'Laporan & Rekap Keuangan')

@section('header_actions')
    <button id="btn-report-print" type="button" class="btn-dark text-sm">
        Cetak / Simpan PDF
    </button>
@endsection

@section('content')
    <div id="report-feedback" role="alert" hidden class="mb-6 print:hidden"></div>

    {{-- Kop resmi yang hanya muncul saat dicetak / disimpan sebagai PDF --}}
    <header class="mb-6 hidden border-b-2 border-ink pb-4 print:block">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-ink" data-report-store-name>ANDARA MOTOR</h1>
                <p class="mt-0.5 text-xs text-muted" data-report-store-address>Showroom Motor Bekas Berkualitas</p>
                <p class="text-xs text-muted" data-report-store-wa></p>
            </div>
            <div class="text-right">
                <p class="text-base font-extrabold uppercase tracking-tight text-ink">Laporan Rekap Penjualan</p>
                <p id="report-print-period" class="mt-0.5 text-xs font-semibold text-muted">Periode: Semua Data</p>
                <p id="report-print-date" class="text-xs text-muted"></p>
            </div>
        </div>
    </header>

    <section aria-label="Filter dan ekspor laporan keuangan" class="mb-6 rounded-lg border border-line bg-white p-4 sm:p-5 print:hidden">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-line pb-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-muted">Preset Cepat:</span>
                <button type="button" data-report-preset="this-month" class="rounded border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink hover:border-ink">
                    Bulan Ini
                </button>
                <button type="button" data-report-preset="this-year" class="rounded border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink hover:border-ink">
                    Tahun Ini
                </button>
                <button type="button" data-report-preset="all" class="rounded border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink hover:border-ink">
                    Semua Periode
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button id="btn-export-excel" type="button" class="btn-wa px-3.5 text-xs">
                    Unduh Excel (.xlsx)
                </button>
                <button id="btn-export-csv" type="button" class="btn-secondary px-3.5 text-xs">
                    Unduh CSV (.csv)
                </button>
            </div>
        </div>

        <form id="admin-report-filter" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="report-start" class="block text-xs font-semibold text-muted">Tanggal Mulai</label>
                <input
                    id="report-start"
                    name="tanggal_mulai"
                    type="date"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums"
                >
            </div>

            <div>
                <label for="report-end" class="block text-xs font-semibold text-muted">Tanggal Selesai</label>
                <input
                    id="report-end"
                    name="tanggal_selesai"
                    type="date"
                    class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums"
                >
            </div>

            <div>
                <label for="report-year" class="block text-xs font-semibold text-muted">Tahun Rekap</label>
                <select id="report-year" name="tahun" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink tabular-nums">
                    <option value="">Semua Tahun</option>
                    @for ($y = (int) date('Y') + 1; $y >= 2022; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="report-method" class="block text-xs font-semibold text-muted">Metode Pembayaran</label>
                <select id="report-method" name="metode_pembayaran" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                    <option value="">Semua Metode</option>
                    <option value="Cash">Cash (Tunai)</option>
                    <option value="Transfer Bank">Transfer Bank</option>
                    <option value="Kredit">Kredit / Leasing</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1 text-sm">Tampilkan</button>
                <button id="report-filter-reset" type="button" class="btn-secondary text-sm">Reset</button>
            </div>
        </form>
    </section>

    <div id="report-root">
        <div class="rounded-lg border border-line bg-white px-6 py-12 text-center text-sm font-medium text-muted" role="status">
            Memuat rekap keuangan showroom...
        </div>
    </div>
@endsection