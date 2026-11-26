@extends('layouts.admin')

@php
    $isEdit = $motorId !== null;
@endphp

@section('page_id', 'admin-motors-form')
@section('title', $isEdit ? 'Edit Unit Motor' : 'Tambah Unit Motor')
@section('page_title', $isEdit ? 'Edit Unit Motor #'.$motorId : 'Tambah Unit Motor Baru')

@section('content')
    <div id="form-error-summary" role="alert" hidden class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900"></div>

    <form id="admin-motor-form" data-motor-id="{{ $motorId ?? '' }}" enctype="multipart/form-data" class="space-y-8">
        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">1. Identitas &amp; Spesifikasi Unit</legend>
            <p class="mt-1 text-xs text-muted">Data utama yang tampil pada kartu katalog dan halaman detail pembeli.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="nama_motor" class="block text-sm font-semibold text-ink">
                        Nama Lengkap Motor <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="nama_motor"
                        name="nama_motor"
                        type="text"
                        required
                        maxlength="255"
                        placeholder="Contoh: Honda Vario 160 CBS Active Black"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div>
                    <label for="merk" class="block text-sm font-semibold text-ink">
                        Merk <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="merk"
                        name="merk"
                        type="text"
                        list="merk-suggestions"
                        required
                        maxlength="100"
                        placeholder="Honda, Yamaha, Kawasaki..."
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                    <datalist id="merk-suggestions">
                        <option value="Honda"></option>
                        <option value="Yamaha"></option>
                        <option value="Suzuki"></option>
                        <option value="Kawasaki"></option>
                        <option value="Vespa"></option>
                    </datalist>
                </div>

                <div>
                    <label for="tipe_motor" class="block text-sm font-semibold text-ink">
                        Tipe Motor <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tipe_motor"
                        name="tipe_motor"
                        type="text"
                        list="tipe-suggestions"
                        required
                        maxlength="100"
                        placeholder="Matic, Sport, Trail, Bebek Sport"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                    <datalist id="tipe-suggestions">
                        <option value="Matic"></option>
                        <option value="Sport"></option>
                        <option value="Trail"></option>
                        <option value="Bebek Sport"></option>
                        <option value="Bebek"></option>
                    </datalist>
                </div>

                <div>
                    <label for="tahun" class="block text-sm font-semibold text-ink">
                        Tahun Pembuatan <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="tahun"
                        name="tahun"
                        type="number"
                        inputmode="numeric"
                        min="1990"
                        max="{{ date('Y') + 1 }}"
                        value="{{ date('Y') }}"
                        required
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                </div>

                <div>
                    <label for="jarak_tempuh" class="block text-sm font-semibold text-ink">
                        Jarak Tempuh (km) <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="jarak_tempuh"
                        name="jarak_tempuh"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        value="0"
                        required
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-ink">
                        Status Unit <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <select id="status" name="status" class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand">
                        <option value="tersedia">Tersedia</option>
                        <option value="booking">Booking (DP)</option>
                        <option value="terjual">Terjual</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">2. Kondisi, Surat, Pajak &amp; Garansi</legend>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="kondisi" class="block text-sm font-semibold text-ink">
                        Kondisi Fisik &amp; Mesin <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="kondisi"
                        name="kondisi"
                        type="text"
                        required
                        placeholder="Contoh: Mulus 98%, mesin halus standar pabrik, ban tebal"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div>
                    <label for="surat_surat" class="block text-sm font-semibold text-ink">
                        Kelengkapan Surat <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="surat_surat"
                        name="surat_surat"
                        type="text"
                        required
                        placeholder="Contoh: Lengkap (BPKB, STNK, Faktur)"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div>
                    <label for="pajak" class="block text-sm font-semibold text-ink">
                        Status Pajak <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="pajak"
                        name="pajak"
                        type="text"
                        required
                        placeholder="Contoh: Hidup (s/d Agustus 2027)"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="garansi" class="block text-sm font-semibold text-ink">Garansi Showroom (Opsional)</label>
                    <input
                        id="garansi"
                        name="garansi"
                        type="text"
                        placeholder="Contoh: Garansi Mesin 1 Bulan"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="deskripsi" class="block text-sm font-semibold text-ink">Deskripsi Tambahan (Opsional)</label>
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="3"
                        placeholder="Riwayat servis, kelengkapan kunci cadangan, atau catatan tambahan."
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                    ></textarea>
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">3. Harga Jual &amp; Modal Internal</legend>
            <p class="mt-1 text-xs text-muted">Harga beli bersifat internal dan tidak pernah dikembalikan pada endpoint API publik.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="harga_jual" class="block text-sm font-semibold text-ink">
                        Harga Jual Publik (Rp) <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="harga_jual"
                        name="harga_jual"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        step="50000"
                        required
                        placeholder="22000000"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 font-semibold text-ink tabular-nums focus:border-brand"
                    >
                </div>

                <div>
                    <label for="harga_beli" class="block text-sm font-semibold text-ink">
                        Harga Beli Modal (Rp) <span class="text-brand" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="harga_beli"
                        name="harga_beli"
                        type="number"
                        inputmode="numeric"
                        min="0"
                        step="50000"
                        required
                        placeholder="18500000"
                        class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink tabular-nums focus:border-brand"
                    >
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-line bg-white p-5 sm:p-6">
            <legend class="px-1 text-base font-bold text-ink">4. Foto Unit</legend>
            <p class="mt-1 text-xs text-muted">Format JPG, PNG, atau WebP, maksimal 2 MB per foto.</p>

            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="foto_utama" class="block text-sm font-semibold text-ink">Foto Utama</label>
                    <input
                        id="foto_utama"
                        name="foto_utama"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink file:mr-4 file:rounded file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white"
                    >
                </div>

                <div>
                    <label for="photos" class="block text-sm font-semibold text-ink">Foto Galeri Tambahan (Opsional)</label>
                    <input
                        id="photos"
                        name="photos[]"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink file:mr-4 file:rounded file:border-0 file:bg-ink file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white"
                    >
                </div>
            </div>

            <div id="photo-preview-box" hidden class="mt-4">
                <p class="mb-2 text-xs font-semibold text-muted">Pratinjau foto utama:</p>
                <img id="photo-preview-img" src="" alt="Pratinjau foto utama unit" width="240" height="180" class="h-36 w-48 rounded-md border border-line object-cover">
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-3">
            <button id="form-submit-btn" type="submit" class="btn-primary">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Unit ke Katalog' }}
            </button>
            <a href="{{ route('admin.motors.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
@endsection