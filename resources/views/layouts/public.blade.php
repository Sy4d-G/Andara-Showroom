<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="@yield('meta_description', 'Showroom motor bekas Andara Motor dengan data surat, pajak, dan odometer transparan.')">
    <meta name="theme-color" content="#111318">
    <title>@yield('title', 'Andara Motor | Showroom Motor Bekas')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="@yield('page_id', 'public-home')" class="flex min-h-screen flex-col bg-white text-ink antialiased @yield('body_class')">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <header class="sticky top-0 z-40 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="shrink-0">
                <x-brand-mark theme="light" />
            </a>

            <search class="hidden flex-1 max-w-xl md:block">
                <form action="{{ route('catalog.index') }}" method="GET" class="flex items-stretch overflow-hidden rounded-md border border-slate-300 bg-white focus-within:border-ink">
                    <label for="header-search-q" class="sr-only">Cari merk atau model motor</label>
                    <input
                        id="header-search-q"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Cari motor, misal Vario, NMAX, Honda..."
                        autocorrect="off"
                        spellcheck="false"
                        class="min-h-11 flex-1 border-0 bg-transparent px-3.5 text-sm text-ink placeholder:text-slate-500 focus:outline-none"
                    >
                    <label for="header-search-tipe" class="sr-only">Tipe motor</label>
                    <select
                        id="header-search-tipe"
                        name="tipe_motor"
                        class="min-h-11 shrink-0 whitespace-nowrap border-0 border-l border-line bg-surface px-3.5 text-sm font-medium text-ink focus:outline-none"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="Matic" @selected(request('tipe_motor') === 'Matic')>Matic</option>
                        <option value="Sport" @selected(request('tipe_motor') === 'Sport')>Sport</option>
                        <option value="Trail" @selected(request('tipe_motor') === 'Trail')>Trail</option>
                        <option value="Bebek Sport" @selected(request('tipe_motor') === 'Bebek Sport')>Bebek Sport</option>
                    </select>
                    <button
                        type="submit"
                        aria-label="Cari unit motor"
                        class="flex min-h-11 w-12 shrink-0 items-center justify-center bg-ink text-white hover:bg-ink-soft"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                    </button>
                </form>
            </search>

            <div class="flex items-center gap-2">
                <a data-public-auth-link href="{{ route('web.login') }}" class="btn-secondary hidden text-sm md:inline-flex">
                    Masuk Admin
                </a>

                <button
                    type="button"
                    data-menu-toggle
                    aria-controls="public-mobile-menu"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-md border border-line bg-white text-ink hover:bg-surface md:hidden"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
        </div>

        <nav aria-label="Navigasi Utama" class="hidden bg-ink md:block">
            <div class="mx-auto flex max-w-7xl items-center gap-1 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Beranda</a>
                <a href="{{ route('catalog.index') }}" @if (request()->routeIs('catalog.index') && ! request('tipe_motor')) aria-current="page" @endif class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Semua Unit</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Matic']) }}" @if (request()->routeIs('catalog.index') && request('tipe_motor') === 'Matic') aria-current="page" @endif class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Motor Matic</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Sport']) }}" @if (request()->routeIs('catalog.index') && request('tipe_motor') === 'Sport') aria-current="page" @endif class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Motor Sport</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Trail']) }}" @if (request()->routeIs('catalog.index') && request('tipe_motor') === 'Trail') aria-current="page" @endif class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Motor Trail</a>
                <a href="{{ route('home') }}#merek" class="nav-link inline-flex min-h-11 items-center px-4 text-sm font-semibold text-slate-300 hover:text-white">Merek</a>
            </div>
        </nav>

        <div id="public-mobile-menu" hidden class="border-t border-white/10 bg-ink px-4 py-4 text-white md:hidden">
            <search>
                <form action="{{ route('catalog.index') }}" method="GET" class="mb-4 flex gap-2">
                    <label for="mobile-search-q" class="sr-only">Cari motor</label>
                    <input
                        id="mobile-search-q"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Cari merk atau model..."
                        class="min-h-11 flex-1 rounded-md border border-white/20 bg-white/10 px-3 text-sm text-white placeholder:text-slate-300"
                    >
                    <button type="submit" class="btn-primary px-4 text-sm">Cari</button>
                </form>
            </search>
            <nav aria-label="Navigasi Mobile" class="space-y-1">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="flex min-h-11 items-center rounded px-3 text-sm font-semibold text-white hover:bg-white/10">Beranda</a>
                <a href="{{ route('catalog.index') }}" class="flex min-h-11 items-center rounded px-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Semua Unit</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Matic']) }}" class="flex min-h-11 items-center rounded px-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Motor Matic</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Sport']) }}" class="flex min-h-11 items-center rounded px-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Motor Sport</a>
                <a href="{{ route('catalog.index', ['tipe_motor' => 'Trail']) }}" class="flex min-h-11 items-center rounded px-3 text-sm font-semibold text-slate-200 hover:bg-white/10">Motor Trail</a>
                <a data-public-auth-link href="{{ route('web.login') }}" class="mt-2 flex min-h-11 items-center justify-center rounded border border-white/20 px-3 text-sm font-semibold text-white hover:bg-white/10">Masuk Admin</a>
            </nav>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 bg-ink text-slate-300">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-12">
                <div class="space-y-3 md:col-span-5">
                    <x-brand-mark theme="dark" />
                    <p class="max-w-sm text-sm leading-relaxed text-slate-300">
                        Showroom motor bekas berkualitas. Spesifikasi surat, pajak, kondisi mesin, dan jarak tempuh ditampilkan transparan sesuai data unit.
                    </p>
                </div>

                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-white">Navigasi</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-white">Katalog Semua Unit</a></li>
                        <li><a href="{{ route('catalog.index', ['tipe_motor' => 'Matic']) }}" class="hover:text-white">Unit Matic</a></li>
                        <li><a href="{{ route('catalog.index', ['tipe_motor' => 'Sport']) }}" class="hover:text-white">Unit Sport</a></li>
                        <li><a data-public-auth-link href="{{ route('web.login') }}" class="hover:text-white">Masuk Panel Admin</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h3 class="text-sm font-bold text-white">Showroom &amp; Kontak</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="sr-only">Alamat</dt>
                            <dd data-store-address>Andara Motor, RT 01/RW 04, Desa Pancawati, Kecamatan Klari, Kabupaten Karawang, Jawa Barat 41371 (seberang pintu masuk Puri Kosambi)</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Jam Operasional</dt>
                            <dd data-store-hours>Buka Setiap Hari: 08:00-18:00 WIB, Kecuali Hari Libur Nasional</dd>
                        </div>
                        <div class="pt-2">
                            <a data-store-wa-link href="https://wa.me/6282298213826" target="_blank" rel="noopener noreferrer" class="btn-wa text-sm">
                                Chat WhatsApp Showroom
                            </a>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} <span data-store-name>Andara Motor</span>. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>
