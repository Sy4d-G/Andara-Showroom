@extends('layouts.admin')

@section('page_id', 'admin-dashboard')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Showroom')

@section('header_actions')
    <a href="{{ route('admin.motors.create') }}" class="btn-primary text-sm">
        Tambah Unit
    </a>
@endsection

@section('content')
    <div id="dashboard-root">
        <div class="rounded-lg border border-line bg-white px-6 py-12 text-center text-sm font-medium text-muted" role="status">
            Memuat statistik dashboard dari API showroom...
        </div>
    </div>
@endsection