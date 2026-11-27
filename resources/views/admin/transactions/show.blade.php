@extends('layouts.admin')

@section('page_id', 'admin-transactions-show')
@section('title', 'Detail & Nota Transaksi #'.$transactionId)
@section('page_title', 'Detail & Nota Transaksi')

@section('header_actions')
    <button id="btn-print-invoice" type="button" class="btn-primary text-sm">
        Cetak Nota / PDF
    </button>
    <a href="{{ route('admin.transactions.index') }}" class="btn-secondary text-sm">
        Kembali
    </a>
@endsection

@section('content')
    <div id="tx-show-feedback" role="status" hidden class="mb-6 print:hidden"></div>

    <div id="tx-detail-root" data-transaction-id="{{ $transactionId }}">
        <div class="rounded-lg border border-line bg-white px-6 py-12 text-center text-sm font-medium text-muted" role="status">
            Memuat detail transaksi dan nota penjualan...
        </div>
    </div>
@endsection