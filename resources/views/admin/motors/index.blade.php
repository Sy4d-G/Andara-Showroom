@extends('layouts.admin')

@section('page_id', 'admin-motors-index')
@section('title', 'Inventori Unit Motor')
@section('page_title', 'Inventori Unit Motor')

@section('header_actions')
    <a href="{{ route('admin.motors.create') }}" class="btn-primary text-sm">
        Tambah Unit
    </a>
@endsection

@section('content')
    <div id="admin-feedback" role="status" hidden></div>

    <section aria-label="Filter inventori motor" class="mb-6 rounded-lg border border-line bg-white p-4">
        <search>
            <form id="admin-motor-filter" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="admin-filter-search" class="block text-xs font-semibold text-muted">Cari Nama / Merk / Tipe</label>
                    <input
                        id="admin-filter-search"
                        name="search"
                        type="search"
                        placeholder="Vario, NMAX, Kawasaki"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink"
                    >
                </div>

                <div>
                    <label for="admin-filter-merk" class="block text-xs font-semibold text-muted">Merk</label>
                    <select id="admin-filter-merk" name="merk" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                        <option value="">Semua Merk</option>
                    </select>
                </div>

                <div>
                    <label for="admin-filter-status" class="block text-xs font-semibold text-muted">Status Stok</label>
                    <select id="admin-filter-status" name="status" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-ink">
                        <option value="">Semua Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="booking">Booking (DP)</option>
                        <option value="terjual">Terjual</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-dark flex-1 text-sm">Terapkan</button>
                    <button id="admin-filter-reset" type="button" class="btn-secondary text-sm">Reset</button>
                </div>
            </form>
        </search>
    </section>

    <section aria-labelledby="admin-motors-heading" class="rounded-lg border border-line bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
            <div>
                <h2 id="admin-motors-heading" class="text-base font-bold text-ink">Daftar Unit Motor</h2>
                <p class="text-xs text-muted">Data terhubung langsung dengan API `/api/admin/motor`.</p>
            </div>
            <span id="admin-motors-count" class="text-xs font-semibold text-muted tabular-nums">...</span>
        </div>

        <div id="admin-motors-table-container">
            <div class="px-6 py-10 text-center text-sm text-muted" role="status">Memuat daftar unit motor...</div>
        </div>
    </section>
@endsection