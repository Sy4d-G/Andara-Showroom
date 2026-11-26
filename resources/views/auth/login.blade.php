<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Masuk Panel Admin | Andara Motor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="auth-login" class="min-h-screen bg-surface text-ink antialiased">
    <a href="#login-main" class="skip-link">Lewati ke form login</a>

    <div class="grid min-h-screen lg:grid-cols-12">
        {{-- Left Showroom Photo Panel --}}
        <div class="relative hidden overflow-hidden bg-ink text-white lg:col-span-5 lg:flex lg:flex-col lg:justify-end">
            <img
                src="{{ asset('images/motorcycles/Foto-untuk-Tampilan-Login.jpeg') }}"
                alt="Showroom Andara Motor Karawang"
                width="900"
                height="1600"
                fetchpriority="high"
                decoding="async"
                class="absolute inset-0 h-full w-full scale-110 object-cover object-center"
            >
            <div
                aria-hidden="true"
                class="absolute inset-0 bg-gradient-to-t from-[#090b0f]/95 via-[#090b0f]/55 to-[#090b0f]/10"
            ></div>

            <div class="relative z-10 space-y-4 p-10">
                <a href="{{ route('home') }}" class="inline-flex">
                    <img
                        src="{{ asset('images/logo/Andara Motor Logo (Merah).png') }}"
                        alt="Andara Motor"
                        width="2172"
                        height="724"
                        decoding="async"
                        class="h-11 w-auto object-contain"
                    >
                </a>

                <h2 class="max-w-md text-3xl font-extrabold leading-tight tracking-tight text-white">
                    Kelola stok motor dan data showroom dalam satu tempat.
                </h2>

                <p data-store-address class="max-w-md text-xs leading-relaxed text-slate-200">
                    Andara Motor, RT 01/RW 04, Desa Pancawati, Kecamatan Klari, Kabupaten Karawang, Jawa Barat 41371 (seberang pintu masuk Puri Kosambi)
                </p>
            </div>
        </div>

        {{-- Right Login Form Column --}}
        <main id="login-main" class="flex flex-col justify-center px-4 py-12 sm:px-8 lg:col-span-7">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex">
                        <img
                            src="{{ asset('images/logo/Andara Motor Logo (Merah).png') }}"
                            alt="Andara Motor"
                            width="2172"
                            height="724"
                            decoding="async"
                            class="h-10 w-auto object-contain"
                        >
                    </a>
                </div>

                <div class="rounded-xl border border-line bg-white p-6 sm:p-8">
                    <h1 class="text-2xl font-extrabold tracking-tight text-ink">Masuk Panel Admin</h1>
                    <p class="mt-1.5 text-sm text-muted">
                        Gunakan email atau username akun Admin <span data-store-name>Andara Motor</span>.
                    </p>

                    <div id="login-error-box" role="alert" hidden class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900"></div>

                    <form id="login-form" class="mt-6 space-y-5" novalidate>
                        <div>
                            <label for="login" class="block text-sm font-semibold text-ink">
                                Email atau Username <span class="text-brand" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="login"
                                name="login"
                                type="text"
                                required
                                autofocus
                                autocomplete="username"
                                autocorrect="off"
                                autocapitalize="none"
                                spellcheck="false"
                                enterkeyhint="next"
                                placeholder="admin@andaramotor.com atau admin"
                                class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink placeholder:text-slate-400 focus:border-brand"
                            >
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-semibold text-ink">
                                    Password <span class="text-brand" aria-hidden="true">*</span>
                                </label>
                                <button
                                    type="button"
                                    data-password-toggle="password"
                                    aria-pressed="false"
                                    class="min-h-8 px-1 text-xs font-semibold text-muted underline hover:text-ink"
                                >
                                    Tampilkan
                                </button>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                enterkeyhint="done"
                                class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-ink focus:border-brand"
                            >
                        </div>

                        <button id="login-submit-btn" type="submit" class="btn-primary w-full">
                            Masuk ke Panel Admin
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-muted hover:text-ink">
                        Kembali ke katalog publik
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
