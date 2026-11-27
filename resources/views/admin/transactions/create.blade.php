@extends('layouts.admin')

@section('page_id', 'admin-transactions-create')
@section('title', 'Catat Transaksi Penjualan')
@section('page_title', 'Catat Transaksi Penjualan')

@section('header_actions')
    <a href="{{ route('admin.transactions.index') }}" class="btn-secondary text-sm">
        Kembali ke Daftar
    </a>
@endsection

@section('content')
    <div id="tx-form-error" role="alert" hidden class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900"></div>

    <form id="admin-tx-create-form" class="space-y-8">
        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">1. Pilih Unit Motor yang Terjual</legend>
            <p class="mt-1 text-xs text-muted">Hanya unit berstatus Tersedia atau Booking (DP) yang dapat dipilih. Setelah disimpan, status unit otomatis berubah menjadi Terjual.</p>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="tx-motor-id" class="block text-sm font-semibold text-ink">
                        Unit Motor <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="tx-motor-id"
                        name="motor_id"
                        required
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                        <option value="">Memuat daftar unit tersedia...</option>
                    </select>
                </div>

                <div id="tx-motor-preview" hidden class="rounded-lg border border-line bg-surface p-4">
                    <div class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-xs font-medium text-muted">Merk / Tipe</p>
                            <p id="tx-preview-merk" class="mt-0.5 font-semibold text-ink">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted">Tahun &amp; Status</p>
                            <p id="tx-preview-status" class="mt-0.5 font-semibold text-ink">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted">Harga Beli Modal</p>
                            <p id="tx-preview-beli" class="mt-0.5 font-semibold text-ink tabular-nums">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted">Harga Katalog</p>
                            <p id="tx-preview-jual" class="mt-0.5 font-bold text-ink tabular-nums">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">2. Rincian Penjualan &amp; Kalkulasi Keuntungan</legend>
            <p class="mt-1 text-xs text-muted">Keuntungan dihitung otomatis dari selisih harga jual deal dikurangi harga beli modal (snapshot).</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="tx-tanggal" class="block text-sm font-semibold text-ink">
                        Tanggal Transaksi <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tx-tanggal"
                        name="tanggal_transaksi"
                        type="date"
                        value="{{ date('Y-m-d') }}"
                        max="{{ date('Y-m-d') }}"
                        required
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                </div>

                <div>
                    <label for="tx-metode" class="block text-sm font-semibold text-ink">
                        Metode Pembayaran <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="tx-metode"
                        name="metode_pembayaran"
                        required
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                        <option value="Cash">Cash (Tunai)</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Kredit">Kredit / Leasing</option>
                    </select>
                </div>

                <div>
                    <label for="tx-harga-beli" class="block text-sm font-semibold text-ink">
                        Harga Beli Modal (Rp) <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tx-harga-beli"
                        name="harga_beli"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        step="50000"
                        required
                        placeholder="18500000"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                    <p class="mt-1 text-xs text-muted">Terisi otomatis dari data modal unit, dapat disesuaikan bila perlu.</p>
                </div>

                <div>
                    <label for="tx-harga-jual" class="block text-sm font-semibold text-ink">
                        Harga Jual Deal (Rp) <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tx-harga-jual"
                        name="harga_jual"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        step="50000"
                        required
                        placeholder="22000000"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 font-semibold text-ink tabular-nums focus:border-brand"
                    >
                    <p class="mt-1 text-xs text-muted">Harga final kesepakatan dengan pembeli.</p>
                </div>

                <div class="sm:col-span-2">
                    <div id="tx-profit-box" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3.5">
                        <div>
                            <p class="text-xs font-semibold text-emerald-900">Estimasi Keuntungan Bersih</p>
                            <p class="text-xs text-emerald-800">Harga Jual Deal &minus; Harga Beli Modal</p>
                        </div>
                        <p id="tx-profit-value" class="text-2xl font-extrabold tracking-tight text-emerald-800 tabular-nums">
                            Rp 0
                        </p>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">3. Data Pembeli &amp; Catatan</legend>
            <p class="mt-1 text-xs text-muted">Data pembeli dicatat sebagai arsip showroom dan tercantum pada nota penjualan.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="tx-nama-pembeli" class="block text-sm font-semibold text-ink">
                        Nama Lengkap Pembeli <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tx-nama-pembeli"
                        name="nama_pembeli"
                        type="text"
                        required
                        maxlength="255"
                        placeholder="Contoh: Dimas Prasetyo"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div>
                    <label for="tx-telepon-pembeli" class="block text-sm font-semibold text-ink">
                        No. Telepon / WhatsApp <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tx-telepon-pembeli"
                        name="no_telepon_pembeli"
                        type="tel"
                        required
                        maxlength="50"
                        placeholder="Contoh: 081288776655"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="tx-alamat-pembeli" class="block text-sm font-semibold text-ink">Alamat Pembeli (Opsional)</label>
                    <textarea
                        id="tx-alamat-pembeli"
                        name="alamat_pembeli"
                        rows="2"
                        placeholder="Contoh: Jl. Margonda Raya No. 45, Depok"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    ></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="tx-catatan" class="block text-sm font-semibold text-ink">Catatan Transaksi (Opsional)</label>
                    <textarea
                        id="tx-catatan"
                        name="catatan"
                        rows="2"
                        placeholder="Contoh: Transfer lunas BCA, bonus helm standar, surat diserahkan lengkap."
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    ></textarea>
                </div>
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-3">
            <button id="tx-submit-btn" type="submit" class="btn-primary">
                Simpan Transaksi &amp; Tandai Terjual
            </button>
            <a href="{{ route('admin.transactions.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@endsection