import {
    apiFetch,
    attachImageFallbacks,
    buildMotorWhatsappUrl,
    clearAuth,
    escapeHtml,
    formatKm,
    formatRupiah,
    getMotorPhotoUrl,
    getStandinPhoto,
    getStoredUser,
    getToken,
    renderMotorCard,
    setAuth,
    statusBadgeHtml,
} from './api.js';

let cachedKontak = null;

const DEFAULT_KONTAK = {
    nama_showroom: 'Andara Motor',
    no_whatsapp: '6282298213826',
    alamat: 'Andara Motor, RT 01/RW 04, Desa Pancawati, Kecamatan Klari, Kabupaten Karawang, Jawa Barat 41371 (seberang pintu masuk Puri Kosambi)',
    jam_operasional: 'Buka Setiap Hari: 08:00-18:00 WIB, Kecuali Hari Libur Nasional',
};

async function loadKontak() {
    if (cachedKontak) {
        return cachedKontak;
    }

    const res = await apiFetch('/kontak');
    if (res.ok && res.data) {
        const rawWa = String(res.data.no_whatsapp || '').replace(/\D+/g, '');
        const rawAlamat = String(res.data.alamat || '');
        const rawJam = String(res.data.jam_operasional || '');

        cachedKontak = {
            ...res.data,
            nama_showroom: res.data.nama_showroom || DEFAULT_KONTAK.nama_showroom,
            no_whatsapp: !rawWa || rawWa === '6281234567890' ? DEFAULT_KONTAK.no_whatsapp : rawWa,
            alamat: !rawAlamat || rawAlamat.includes('Andara Raya No. 88') ? DEFAULT_KONTAK.alamat : rawAlamat,
            jam_operasional: !rawJam || rawJam.includes('Senin - Sabtu') ? DEFAULT_KONTAK.jam_operasional : rawJam,
        };
    } else {
        cachedKontak = { ...DEFAULT_KONTAK };
    }

    return cachedKontak;
}

function hydrateShellKontak(kontak) {
    const phone = String(kontak.no_whatsapp || DEFAULT_KONTAK.no_whatsapp).replace(/\D+/g, '');
    const waText = encodeURIComponent(`Halo ${kontak.nama_showroom}, saya ingin menanyakan stok motor bekas yang tersedia.`);
    const waUrl = `https://wa.me/${phone}?text=${waText}`;

    document.querySelectorAll('[data-store-name]').forEach((el) => {
        el.textContent = kontak.nama_showroom;
    });
    document.querySelectorAll('[data-store-address]').forEach((el) => {
        el.textContent = kontak.alamat;
    });
    document.querySelectorAll('[data-store-hours]').forEach((el) => {
        el.textContent = kontak.jam_operasional;
    });
    document.querySelectorAll('[data-store-wa-link]').forEach((el) => {
        el.setAttribute('href', waUrl);
    });
}

function setupCommonControls() {
    // Update auth link label in public header if admin token is already saved
    const hasToken = Boolean(getToken());
    document.querySelectorAll('[data-public-auth-link]').forEach((link) => {
        if (hasToken) {
            link.setAttribute('href', '/admin');
            link.textContent = 'Panel Admin';
        }
    });

    // Mobile drawer / menu toggles with aria-expanded + Escape focus restoration
    document.querySelectorAll('[data-menu-toggle]').forEach((toggle) => {
        const targetId = toggle.getAttribute('aria-controls');
        const menu = targetId ? document.getElementById(targetId) : null;
        if (!menu) {
            return;
        }

        const closeMenu = () => {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') === 'true';
            menu.hidden = open;
            toggle.setAttribute('aria-expanded', String(!open));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                closeMenu();
                toggle.focus();
            }
        });
    });

    // Accessible password visibility toggle
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.getAttribute('data-password-toggle') || '');
        if (!input) {
            return;
        }
        button.addEventListener('click', () => {
            const pressed = button.getAttribute('aria-pressed') === 'true';
            input.type = pressed ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!pressed));
            button.textContent = pressed ? 'Tampilkan' : 'Sembunyikan';
        });
    });

    // Admin logout buttons
    document.querySelectorAll('[data-admin-logout]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            await apiFetch('/logout', { method: 'POST' });
            clearAuth();
            window.location.href = '/login';
        });
    });
}

async function initPublicHome() {
    const kontak = await loadKontak();
    hydrateShellKontak(kontak);

    // Pause / play control for brand logo marquee (R-39)
    const marquee = document.getElementById('brand-marquee');
    const marqueeToggle = document.getElementById('brand-marquee-toggle');
    if (marquee && marqueeToggle) {
        marqueeToggle.addEventListener('click', () => {
            const paused = marquee.classList.toggle('is-paused');
            marqueeToggle.setAttribute('aria-pressed', String(paused));
            marqueeToggle.textContent = paused ? 'Putar' : 'Jeda';
            marqueeToggle.setAttribute('aria-label', paused ? 'Putar animasi logo merek' : 'Jeda animasi logo merek');
        });
    }

    const motorsRes = await apiFetch('/motor?per_page=6&sort_by=created_at&sort_order=desc');

    const grid = document.getElementById('home-motors-grid');
    if (grid) {
        if (!motorsRes.ok) {
            grid.innerHTML = `
                <div role="alert" class="col-span-full rounded-xl border border-red-200 bg-red-50 px-6 py-10 text-center">
                    <p class="text-base font-bold text-red-900">Gagal memuat daftar motor</p>
                    <p class="mt-1 text-sm text-red-800">${escapeHtml(motorsRes.message)}</p>
                    <div class="mt-4">
                        <button type="button" onclick="window.location.reload()" class="btn-primary text-sm">Muat Ulang</button>
                    </div>
                </div>
            `;
        } else if (!Array.isArray(motorsRes.data) || motorsRes.data.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full rounded-xl border border-line bg-surface px-6 py-12 text-center">
                    <p class="text-lg font-bold text-ink">Belum ada unit tersedia saat ini</p>
                    <p class="mt-1 text-sm text-muted">Hubungi WhatsApp showroom kami untuk menanyakan unit yang baru masuk hari ini.</p>
                </div>
            `;
        } else {
            grid.innerHTML = motorsRes.data.map((motor) => renderMotorCard(motor, kontak)).join('');
            attachImageFallbacks(grid);
        }
    }
}

async function initPublicCatalog() {
    const kontak = await loadKontak();
    hydrateShellKontak(kontak);

    const form = document.getElementById('catalog-filter-form');
    const grid = document.getElementById('catalog-motors-grid');
    const summary = document.getElementById('catalog-summary');
    const pagination = document.getElementById('catalog-pagination');
    const resetLink = document.getElementById('catalog-reset-btn');

    const params = new URLSearchParams(window.location.search);

    // Populate merk & tipe_motor options from /api/motor/meta/filters
    const metaRes = await apiFetch('/motor/meta/filters');
    if (metaRes.ok && metaRes.data) {
        const merkSelect = document.getElementById('filter-merk');
        if (merkSelect && Array.isArray(metaRes.data.merks)) {
            merkSelect.innerHTML = '<option value="">Semua Merk</option>' +
                metaRes.data.merks.map((m) => `<option value="${escapeHtml(m)}">${escapeHtml(m)}</option>`).join('');
        }
        const tipeSelect = document.getElementById('filter-tipe');
        if (tipeSelect && Array.isArray(metaRes.data.tipe_motors)) {
            tipeSelect.innerHTML = '<option value="">Semua Tipe</option>' +
                metaRes.data.tipe_motors.map((t) => `<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join('');
        }
    }

    // Sync initial URL params into filter inputs
    const fieldMap = ['search', 'merk', 'tipe_motor', 'tahun_min', 'tahun_max', 'harga_min', 'harga_max', 'status'];
    fieldMap.forEach((key) => {
        const input = form?.elements.namedItem(key);
        if (input && params.get(key)) {
            input.value = params.get(key);
        }
    });

    const sortPreset = document.getElementById('filter-sort');
    if (sortPreset && params.get('sort_by')) {
        const key = `${params.get('sort_by')}:${params.get('sort_order') || 'desc'}`;
        if ([...sortPreset.options].some((o) => o.value === key)) {
            sortPreset.value = key;
        }
    }

    async function fetchAndRender(page = 1) {
        const query = new URLSearchParams();
        if (form) {
            const fd = new FormData(form);
            for (const [key, rawVal] of fd.entries()) {
                const val = String(rawVal).trim();
                if (!val) {
                    continue;
                }
                if (key === 'sort') {
                    const [sortBy, sortOrder] = val.split(':');
                    if (sortBy) {
                        query.set('sort_by', sortBy);
                    }
                    if (sortOrder) {
                        query.set('sort_order', sortOrder);
                    }
                } else {
                    query.set(key, val);
                }
            }
        }
        if (page > 1) {
            query.set('page', String(page));
        }

        const qs = query.toString();
        window.history.replaceState({}, '', qs ? `/motor?${qs}` : '/motor');
        if (resetLink) {
            resetLink.hidden = !qs;
        }

        if (grid) {
            grid.innerHTML = `
                <div class="col-span-full py-12 text-center text-sm font-medium text-muted" role="status">
                    Memuat daftar unit motor dari katalog showroom...
                </div>
            `;
        }

        const res = await apiFetch(`/motor${qs ? `?${qs}` : ''}`);
        if (!res.ok) {
            if (summary) {
                summary.textContent = 'Gagal memuat data katalog.';
            }
            if (grid) {
                grid.innerHTML = `
                    <div role="alert" class="col-span-full rounded-xl border border-red-200 bg-red-50 px-6 py-10 text-center">
                        <p class="text-base font-bold text-red-900">Tidak dapat mengambil data katalog</p>
                        <p class="mt-1 text-sm text-red-800">${escapeHtml(res.message)}</p>
                    </div>
                `;
            }
            return;
        }

        const total = res.meta?.total ?? (res.data?.length ?? 0);
        if (summary) {
            summary.innerHTML = `Menampilkan stok real-time showroom. Total <span class="font-bold text-ink tabular-nums">${total} unit</span> ditemukan.`;
        }

        if (!Array.isArray(res.data) || res.data.length === 0) {
            if (grid) {
                grid.innerHTML = `
                    <div class="col-span-full rounded-xl border border-line bg-surface px-6 py-16 text-center">
                        <p class="text-lg font-bold text-ink">Unit tidak ditemukan</p>
                        <p class="mt-1.5 text-sm text-muted">Tidak ada motor yang cocok dengan filter Anda. Ubah kriteria atau reset semua filter.</p>
                        <div class="mt-6">
                            <a href="/motor" class="btn-primary">Reset Semua Filter</a>
                        </div>
                    </div>
                `;
            }
            if (pagination) {
                pagination.innerHTML = '';
            }
            return;
        }

        if (grid) {
            grid.innerHTML = res.data.map((motor) => renderMotorCard(motor, kontak)).join('');
            attachImageFallbacks(grid);
        }

        if (pagination && res.meta && res.meta.last_page > 1) {
            const buttons = [];
            for (let p = 1; p <= res.meta.last_page; p += 1) {
                const active = p === res.meta.current_page;
                buttons.push(`
                    <button
                        type="button"
                        data-page-btn="${p}"
                        aria-current="${active ? 'page' : 'false'}"
                        class="${active ? 'btn-primary' : 'btn-secondary'} min-w-11 px-3 text-sm tabular-nums"
                    >${p}</button>
                `);
            }
            pagination.innerHTML = `<nav aria-label="Navigasi halaman katalog" class="flex flex-wrap items-center justify-center gap-2">${buttons.join('')}</nav>`;
            pagination.querySelectorAll('[data-page-btn]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    fetchAndRender(Number(btn.getAttribute('data-page-btn')));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        } else if (pagination) {
            pagination.innerHTML = '';
        }
    }

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchAndRender(1);
    });

    await fetchAndRender(Number(params.get('page') || 1));
}

async function initPublicDetail() {
    const kontak = await loadKontak();
    hydrateShellKontak(kontak);

    const root = document.getElementById('detail-root');
    const motorId = root?.getAttribute('data-motor-id');
    if (!root || !motorId) {
        return;
    }

    const res = await apiFetch(`/motor/${encodeURIComponent(motorId)}`);
    if (!res.ok || !res.data) {
        root.innerHTML = `
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-6 py-12 text-center">
                <h1 class="text-xl font-extrabold text-red-900">Unit motor tidak ditemukan</h1>
                <p class="mt-2 text-sm text-red-800">${escapeHtml(res.message || 'Data unit mungkin sudah dihapus atau ID tidak valid.')}</p>
                <div class="mt-6">
                    <a href="/motor" class="btn-primary">Kembali ke Katalog</a>
                </div>
            </div>
        `;
        return;
    }

    const motor = res.data;
    document.title = `${motor.nama_motor} ${motor.tahun} | ${kontak.nama_showroom}`;
    const isSold = motor.status === 'terjual';
    const waUrl = buildMotorWhatsappUrl(motor, kontak);
    const mainPhoto = getMotorPhotoUrl(motor);
    const fallbackUrl = getStandinPhoto(motor.id);
    const galleryPhotos = [
        { url: mainPhoto, label: `${motor.nama_motor} tampak utama` },
        ...(Array.isArray(motor.photos)
            ? motor.photos.map((p, idx) => ({
                  url: p.foto_url || fallbackUrl,
                  label: p.caption || `${motor.nama_motor} foto ${idx + 2}`,
              }))
            : []),
    ];

    const taxIsDead = String(motor.pajak || '').toLowerCase().includes('mati');

    root.innerHTML = `
        <nav aria-label="Breadcrumb" class="mb-5 flex flex-wrap items-center gap-2 text-xs font-medium text-muted">
            <a href="/" class="hover:text-ink">Beranda</a>
            <span aria-hidden="true">/</span>
            <a href="/motor" class="hover:text-ink">Katalog</a>
            <span aria-hidden="true">/</span>
            <span class="font-semibold text-ink">${escapeHtml(motor.nama_motor)}</span>
        </nav>

        ${isSold ? `
            <div role="status" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-red-950">
                <p class="text-base font-extrabold tracking-tight text-red-800">UNIT INI SUDAH TERJUAL</p>
                <p class="mt-1 text-sm text-red-900">Unit ${escapeHtml(motor.nama_motor)} sudah laku terjual. Lihat rekomendasi unit lain yang masih tersedia di bawah halaman ini.</p>
            </div>
        ` : ''}

        <div class="grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-xl border border-line bg-surface">
                    <img
                        id="detail-main-photo"
                        src="${escapeHtml(mainPhoto)}"
                        alt="${escapeHtml(motor.nama_motor)} tahun ${escapeHtml(motor.tahun)}"
                        width="960"
                        height="720"
                        fetchpriority="high"
                        decoding="async"
                        data-fallback="${escapeHtml(fallbackUrl)}"
                        class="aspect-[4/3] w-full object-cover"
                    >
                </div>

                ${galleryPhotos.length > 1 ? `
                    <div class="mt-3 grid grid-cols-4 gap-2.5 sm:grid-cols-5">
                        ${galleryPhotos.map((photo, idx) => `
                            <button
                                type="button"
                                data-gallery-thumb="${escapeHtml(photo.url)}"
                                aria-label="Tampilkan ${escapeHtml(photo.label)}"
                                aria-pressed="${idx === 0 ? 'true' : 'false'}"
                                class="overflow-hidden rounded-md border ${idx === 0 ? 'border-brand ring-2 ring-brand/20' : 'border-line'} bg-surface"
                            >
                                <img
                                    src="${escapeHtml(photo.url)}"
                                    alt="${escapeHtml(photo.label)}"
                                    width="160"
                                    height="120"
                                    loading="lazy"
                                    data-fallback="${escapeHtml(fallbackUrl)}"
                                    class="aspect-[4/3] w-full object-cover"
                                >
                            </button>
                        `).join('')}
                    </div>
                ` : ''}

                <section aria-labelledby="catatan-unit-heading" class="mt-6 rounded-xl border border-line bg-white p-5 sm:p-6">
                    <h2 id="catatan-unit-heading" class="text-base font-bold text-ink">Kondisi &amp; Deskripsi Unit</h2>
                    <p class="mt-2.5 text-sm leading-relaxed text-ink">${escapeHtml(motor.kondisi)}</p>
                    ${motor.deskripsi ? `<p class="mt-3 border-t border-line pt-3 text-sm leading-relaxed text-muted">${escapeHtml(motor.deskripsi)}</p>` : ''}
                </section>
            </div>

            <div class="lg:col-span-5">
                <div class="rounded-xl border border-line bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        ${statusBadgeHtml(motor.status)}
                        <span class="text-xs font-semibold text-muted tabular-nums">ID Unit: #${escapeHtml(motor.id)}</span>
                    </div>

                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                        ${escapeHtml(motor.nama_motor)}
                    </h1>

                    <div class="mt-4 border-y border-line py-4">
                        <p class="text-xs font-semibold text-muted">Harga Jual</p>
                        <p class="mt-1 text-3xl font-extrabold tracking-tight text-ink tabular-nums">
                            ${escapeHtml(motor.harga_jual_formatted || formatRupiah(motor.harga_jual))}
                        </p>
                    </div>

                    ${!isSold ? `
                        <div class="mt-5 space-y-2.5">
                            <a href="${escapeHtml(waUrl)}" target="_blank" rel="noopener noreferrer" class="btn-wa w-full text-base">
                                Tanya via WhatsApp
                            </a>
                            <p class="text-center text-xs text-muted">
                                Pesan WhatsApp otomatis mencantumkan nama motor, tahun, dan harga unit ini.
                            </p>
                        </div>
                    ` : ''}

                    <h2 class="mt-6 text-sm font-bold text-ink">Spesifikasi &amp; Kelengkapan</h2>
                    <dl class="mt-3 divide-y divide-line text-sm">
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Merk</dt>
                            <dd class="text-right font-semibold text-ink">${escapeHtml(motor.merk)}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Tipe Motor</dt>
                            <dd class="text-right font-semibold text-ink">${escapeHtml(motor.tipe_motor)}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Tahun</dt>
                            <dd class="font-semibold text-ink tabular-nums">${escapeHtml(motor.tahun)}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Jarak Tempuh (Odometer)</dt>
                            <dd class="font-semibold text-ink tabular-nums">${escapeHtml(formatKm(motor.jarak_tempuh))}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Kelengkapan Surat</dt>
                            <dd class="text-right font-semibold text-ink">${escapeHtml(motor.surat_surat)}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Status Pajak</dt>
                            <dd class="text-right font-semibold ${taxIsDead ? 'text-brand' : 'text-ink'}">${escapeHtml(motor.pajak)}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-2.5">
                            <dt class="text-muted">Garansi</dt>
                            <dd class="text-right font-semibold text-ink">${escapeHtml(motor.garansi || 'Tidak ada garansi khusus')}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <section id="related-section" aria-labelledby="related-heading" class="mt-14 border-t border-line pt-10" hidden>
            <div class="mb-6 flex items-end justify-between gap-4">
                <h2 id="related-heading" class="text-xl font-extrabold tracking-tight text-ink">
                    ${isSold ? 'Rekomendasi Unit Lain yang Tersedia' : 'Unit Lain yang Mungkin Cocok'}
                </h2>
                <a href="/motor" class="text-sm font-semibold text-brand hover:underline">Lihat Semua</a>
            </div>
            <div id="related-grid" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4"></div>
        </section>
    `;

    attachImageFallbacks(root);

    // Sticky mobile WhatsApp bar
    const mobileBar = document.getElementById('mobile-wa-bar');
    if (mobileBar && !isSold) {
        mobileBar.hidden = false;
        const priceEl = document.getElementById('mobile-wa-price');
        const linkEl = document.getElementById('mobile-wa-link');
        if (priceEl) {
            priceEl.textContent = motor.harga_jual_formatted || formatRupiah(motor.harga_jual);
        }
        if (linkEl) {
            linkEl.setAttribute('href', waUrl);
        }
    }

    // Gallery switcher
    const mainImg = document.getElementById('detail-main-photo');
    root.querySelectorAll('[data-gallery-thumb]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const nextSrc = btn.getAttribute('data-gallery-thumb');
            if (mainImg && nextSrc) {
                mainImg.src = nextSrc;
            }
            root.querySelectorAll('[data-gallery-thumb]').forEach((item) => {
                item.setAttribute('aria-pressed', 'false');
                item.classList.remove('border-brand', 'ring-2', 'ring-brand/20');
                item.classList.add('border-line');
            });
            btn.setAttribute('aria-pressed', 'true');
            btn.classList.add('border-brand', 'ring-2', 'ring-brand/20');
            btn.classList.remove('border-line');
        });
    });

    // Load related available units
    const relatedRes = await apiFetch(`/motor?merk=${encodeURIComponent(motor.merk)}&per_page=5`);
    const relatedList = (relatedRes.ok && Array.isArray(relatedRes.data) ? relatedRes.data : [])
        .filter((item) => Number(item.id) !== Number(motor.id))
        .slice(0, 4);

    if (relatedList.length > 0) {
        const relSec = document.getElementById('related-section');
        const relGrid = document.getElementById('related-grid');
        if (relSec && relGrid) {
            relSec.hidden = false;
            relGrid.innerHTML = relatedList.map((item) => renderMotorCard(item, kontak)).join('');
            attachImageFallbacks(relGrid);
        }
    }
}

async function initAuthLogin() {
    if (getToken()) {
        const me = await apiFetch('/me');
        if (me.ok) {
            window.location.href = '/admin';
            return;
        }
        clearAuth();
    }

    const kontak = await loadKontak();
    hydrateShellKontak(kontak);

    const form = document.getElementById('login-form');
    const errorBox = document.getElementById('login-error-box');
    const submitBtn = document.getElementById('login-submit-btn');

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (errorBox) {
            errorBox.hidden = true;
            errorBox.textContent = '';
        }

        const login = String(form.elements.namedItem('login')?.value || '').trim();
        const password = String(form.elements.namedItem('password')?.value || '');

        if (!login || !password) {
            if (errorBox) {
                errorBox.hidden = false;
                errorBox.textContent = 'Username/email dan password wajib diisi.';
            }
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memeriksa...';
        }

        const res = await apiFetch('/login', {
            method: 'POST',
            body: JSON.stringify({ login, password }),
        });

        if (!res.ok || !res.data?.token) {
            if (errorBox) {
                errorBox.hidden = false;
                errorBox.textContent = res.message || 'Kredensial login tidak valid.';
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Masuk ke Panel Admin';
            }
            return;
        }

        setAuth(res.data.token, res.data.user);
        window.location.href = '/admin';
    });
}

// =============================================================================
// Bagian Panel Admin (di-comment dulu — diserahkan ke anggota tim berikutnya)
// =============================================================================
// async function ensureAdminAuthenticated() {
//     if (!getToken()) {
//         window.location.href = '/login';
//         return null;
//     }
//
//     const meRes = await apiFetch('/me');
//     if (!meRes.ok || !meRes.data) {
//         clearAuth();
//         window.location.href = '/login';
//         return null;
//     }
//
//     setAuth(getToken(), meRes.data);
//     document.querySelectorAll('[data-admin-name]').forEach((el) => {
//         el.textContent = meRes.data.name || 'Admin';
//     });
//     document.querySelectorAll('[data-admin-email]').forEach((el) => {
//         el.textContent = meRes.data.email || '';
//     });
//
//     return meRes.data;
// }
//
// async function initAdminDashboard() {
//     const user = await ensureAdminAuthenticated();
//     if (!user) {
//         return;
//     }
//
//     const root = document.getElementById('dashboard-root');
//     const res = await apiFetch('/admin/dashboard');
//     if (!root) {
//         return;
//     }
//
//     if (!res.ok || !res.data) {
//         root.innerHTML = `
//             <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-5 text-sm font-medium text-red-900">
//                 Gagal memuat statistik dashboard: ${escapeHtml(res.message)}
//             </div>
//         `;
//         return;
//     }
//
//     const { ringkasan_unit: unit, ringkasan_keuangan: keu, performa_bulan_ini: bulan, motor_terbaru: motors, transaksi_terbaru: tx } = res.data;
//
//     root.innerHTML = `
//         <section aria-labelledby="unit-summary-heading">
//             <h2 id="unit-summary-heading" class="text-lg font-bold text-ink">Ringkasan Stok Unit</h2>
//             <p class="text-sm text-muted">Data langsung dari API dashboard showroom.</p>
//
//             <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Unit Tersedia</p>
//                     <p class="mt-2 text-3xl font-extrabold text-ink tabular-nums">${escapeHtml(unit.motor_tersedia)}</p>
//                     <p class="mt-1 text-xs text-emerald-700">Aktif di katalog publik</p>
//                 </div>
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Unit Booking (DP)</p>
//                     <p class="mt-2 text-3xl font-extrabold text-ink tabular-nums">${escapeHtml(unit.motor_booking)}</p>
//                     <p class="mt-1 text-xs text-amber-800">Menunggu pelunasan</p>
//                 </div>
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Unit Terjual</p>
//                     <p class="mt-2 text-3xl font-extrabold text-ink tabular-nums">${escapeHtml(unit.motor_terjual)}</p>
//                     <p class="mt-1 text-xs text-muted">Total tercatat terjual</p>
//                 </div>
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Total Aset Stok</p>
//                     <p class="mt-2 text-xl font-extrabold text-ink tabular-nums">${escapeHtml(keu.total_aset_tersedia_formatted)}</p>
//                     <p class="mt-1 text-xs text-emerald-700">Estimasi laba: ${escapeHtml(keu.estimasi_potensi_laba_tersedia_formatted)}</p>
//                 </div>
//             </div>
//
//             <div class="mt-4 grid gap-4 sm:grid-cols-3">
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Total Omset Penjualan</p>
//                     <p class="mt-1.5 text-xl font-extrabold text-ink tabular-nums">${escapeHtml(keu.total_omset_formatted)}</p>
//                 </div>
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Total Keuntungan Bersih</p>
//                     <p class="mt-1.5 text-xl font-extrabold text-emerald-700 tabular-nums">${escapeHtml(keu.total_keuntungan_formatted)}</p>
//                 </div>
//                 <div class="rounded-lg border border-line bg-white p-5">
//                     <p class="text-xs font-semibold text-muted">Performa ${escapeHtml(bulan.bulan)}</p>
//                     <p class="mt-1.5 text-xl font-extrabold text-ink tabular-nums">${escapeHtml(bulan.unit_terjual)} unit &middot; ${escapeHtml(bulan.keuntungan_formatted)}</p>
//                 </div>
//             </div>
//         </section>
//
//         <section aria-labelledby="recent-motors-heading" class="mt-8 rounded-lg border border-line bg-white">
//             <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
//                 <div>
//                     <h2 id="recent-motors-heading" class="text-base font-bold text-ink">5 Motor Masuk Terbaru</h2>
//                     <p class="text-xs text-muted">Harga beli hanya ditampilkan di panel admin.</p>
//                 </div>
//                 <a href="/admin/motor" class="text-sm font-semibold text-brand hover:underline">Kelola semua unit</a>
//             </div>
//             <div tabindex="0" role="region" aria-labelledby="recent-motors-heading" class="table-wrapper">
//                 <table class="data-table data-table--stack text-left text-sm">
//                     <thead>
//                         <tr class="border-b border-line text-xs font-semibold text-muted">
//                             <th scope="col" class="px-5 py-3">Unit</th>
//                             <th scope="col" class="px-4 py-3">Tahun</th>
//                             <th scope="col" class="px-4 py-3">Harga Beli</th>
//                             <th scope="col" class="px-4 py-3">Harga Jual</th>
//                             <th scope="col" class="px-4 py-3">Status</th>
//                             <th scope="col" class="px-5 py-3 text-right">Aksi</th>
//                         </tr>
//                     </thead>
//                     <tbody class="divide-y divide-line">
//                         ${motors.map((m) => `
//                             <tr class="hover:bg-surface/60">
//                                 <th scope="row" data-label="Unit" class="px-5 py-3.5 font-bold text-ink">${escapeHtml(m.nama_motor)}</th>
//                                 <td data-label="Tahun" class="px-4 py-3.5 text-muted tabular-nums">${escapeHtml(m.tahun)}</td>
//                                 <td data-label="Harga Beli" class="px-4 py-3.5 text-muted tabular-nums">${escapeHtml(formatRupiah(m.harga_beli))}</td>
//                                 <td data-label="Harga Jual" class="px-4 py-3.5 font-bold text-ink tabular-nums">${escapeHtml(formatRupiah(m.harga_jual))}</td>
//                                 <td data-label="Status" class="px-4 py-3.5">${statusBadgeHtml(m.status)}</td>
//                                 <td data-label="Aksi" class="px-5 py-3.5 text-right">
//                                     <a href="/admin/motor/${encodeURIComponent(m.id)}/edit" class="rounded border border-line bg-white px-2.5 py-1.5 text-xs font-semibold text-ink hover:bg-surface">Edit</a>
//                                 </td>
//                             </tr>
//                         `).join('')}
//                     </tbody>
//                 </table>
//             </div>
//         </section>
//
//         <section aria-labelledby="recent-tx-heading" class="mt-8 rounded-lg border border-line bg-white">
//             <div class="border-b border-line px-5 py-4">
//                 <h2 id="recent-tx-heading" class="text-base font-bold text-ink">Transaksi Penjualan Terbaru</h2>
//             </div>
//             ${tx.length === 0 ? `
//                 <div class="px-6 py-10 text-center text-sm text-muted">Belum ada transaksi penjualan yang tercatat.</div>
//             ` : `
//                 <div tabindex="0" role="region" aria-labelledby="recent-tx-heading" class="table-wrapper">
//                     <table class="data-table data-table--stack text-left text-sm">
//                         <thead>
//                             <tr class="border-b border-line text-xs font-semibold text-muted">
//                                 <th scope="col" class="px-5 py-3">Kode</th>
//                                 <th scope="col" class="px-4 py-3">Tanggal</th>
//                                 <th scope="col" class="px-4 py-3">Motor</th>
//                                 <th scope="col" class="px-4 py-3">Pembeli</th>
//                                 <th scope="col" class="px-4 py-3">Harga Jual</th>
//                                 <th scope="col" class="px-5 py-3 text-right">Keuntungan</th>
//                             </tr>
//                         </thead>
//                         <tbody class="divide-y divide-line">
//                             ${tx.map((t) => `
//                                 <tr class="hover:bg-surface/60">
//                                     <th scope="row" data-label="Kode" class="px-5 py-3.5 font-semibold text-ink tabular-nums">${escapeHtml(t.kode_transaksi)}</th>
//                                     <td data-label="Tanggal" class="px-4 py-3.5 text-muted tabular-nums">${escapeHtml(t.tanggal)}</td>
//                                     <td data-label="Motor" class="px-4 py-3.5 font-medium text-ink">${escapeHtml(t.motor)}</td>
//                                     <td data-label="Pembeli" class="px-4 py-3.5 text-muted">${escapeHtml(t.nama_pembeli)}</td>
//                                     <td data-label="Harga Jual" class="px-4 py-3.5 font-semibold text-ink tabular-nums">${escapeHtml(t.harga_jual_formatted)}</td>
//                                     <td data-label="Keuntungan" class="px-5 py-3.5 text-right font-bold text-emerald-700 tabular-nums">${escapeHtml(t.keuntungan_formatted)}</td>
//                                 </tr>
//                             `).join('')}
//                         </tbody>
//                     </table>
//                 </div>
//             `}
//         </section>
//     `;
// }
//
// async function initAdminMotorsIndex() {
//     const user = await ensureAdminAuthenticated();
//     if (!user) {
//         return;
//     }
//
//     const filterForm = document.getElementById('admin-motor-filter');
//     const merkSelect = document.getElementById('admin-filter-merk');
//     const tableContainer = document.getElementById('admin-motors-table-container');
//     const countBadge = document.getElementById('admin-motors-count');
//     const feedback = document.getElementById('admin-feedback');
//
//     const metaRes = await apiFetch('/motor/meta/filters');
//     if (metaRes.ok && merkSelect && Array.isArray(metaRes.data?.merks)) {
//         merkSelect.innerHTML = '<option value="">Semua Merk</option>' +
//             metaRes.data.merks.map((m) => `<option value="${escapeHtml(m)}">${escapeHtml(m)}</option>`).join('');
//     }
//
//     function showFeedback(type, message) {
//         if (!feedback) {
//             return;
//         }
//         feedback.hidden = false;
//         feedback.className = type === 'error'
//             ? 'mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900'
//             : 'mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900';
//         feedback.textContent = message;
//     }
//
//     async function loadMotors(page = 1) {
//         const query = new URLSearchParams({ per_page: '15', page: String(page) });
//         if (filterForm) {
//             const fd = new FormData(filterForm);
//             for (const [k, v] of fd.entries()) {
//                 if (String(v).trim()) {
//                     query.set(k, String(v).trim());
//                 }
//             }
//         }
//
//         if (tableContainer) {
//             tableContainer.innerHTML = '<div class="px-6 py-10 text-center text-sm text-muted" role="status">Memuat daftar unit motor...</div>';
//         }
//
//         const res = await apiFetch(`/admin/motor?${query.toString()}`);
//         if (!res.ok) {
//             if (tableContainer) {
//                 tableContainer.innerHTML = `<div role="alert" class="px-6 py-10 text-center text-sm font-medium text-red-800">${escapeHtml(res.message)}</div>`;
//             }
//             return;
//         }
//
//         const items = Array.isArray(res.data) ? res.data : [];
//         if (countBadge) {
//             countBadge.textContent = `${res.meta?.total ?? items.length} unit`;
//         }
//
//         if (items.length === 0) {
//             tableContainer.innerHTML = `
//                 <div class="px-6 py-12 text-center">
//                     <p class="text-base font-bold text-ink">Tidak ada unit motor ditemukan</p>
//                     <p class="mt-1 text-sm text-muted">Tambahkan unit baru atau reset filter pencarian Anda.</p>
//                     <div class="mt-5">
//                         <a href="/admin/motor/create" class="btn-primary">Tambah Unit Motor</a>
//                     </div>
//                 </div>
//             `;
//             return;
//         }
//
//         tableContainer.innerHTML = `
//             <div tabindex="0" role="region" aria-labelledby="admin-motors-heading" class="table-wrapper">
//                 <table class="data-table data-table--stack text-left text-sm">
//                     <thead>
//                         <tr class="border-b border-line text-xs font-semibold text-muted">
//                             <th scope="col" class="px-5 py-3">Unit</th>
//                             <th scope="col" class="px-4 py-3">Tahun / KM</th>
//                             <th scope="col" class="px-4 py-3">Harga Beli</th>
//                             <th scope="col" class="px-4 py-3">Harga Jual</th>
//                             <th scope="col" class="px-4 py-3">Potensi Laba</th>
//                             <th scope="col" class="px-4 py-3">Status</th>
//                             <th scope="col" class="px-5 py-3 text-right">Aksi</th>
//                         </tr>
//                     </thead>
//                     <tbody class="divide-y divide-line">
//                         ${items.map((m) => {
//                             const photo = escapeHtml(getMotorPhotoUrl(m));
//                             const fallback = escapeHtml(getStandinPhoto(m.id));
//                             const nextStatus = m.status === 'tersedia' ? 'booking' : 'tersedia';
//                             const nextLabel = nextStatus === 'booking' ? 'Tandai Booking' : 'Tandai Tersedia';
//                             return `
//                                 <tr class="hover:bg-surface/60">
//                                     <th scope="row" data-label="Unit" class="px-5 py-3.5">
//                                         <div class="flex items-center gap-3">
//                                             <img src="${photo}" data-fallback="${fallback}" alt="${escapeHtml(m.nama_motor)}" width="64" height="48" loading="lazy" class="h-12 w-16 shrink-0 rounded border border-line object-cover">
//                                             <div class="text-left">
//                                                 <p class="font-bold text-ink">${escapeHtml(m.nama_motor)}</p>
//                                                 <p class="text-xs text-muted">${escapeHtml(m.merk)} &middot; ${escapeHtml(m.tipe_motor)}</p>
//                                             </div>
//                                         </div>
//                                     </th>
//                                     <td data-label="Tahun / KM" class="px-4 py-3.5 text-muted tabular-nums">${escapeHtml(m.tahun)} &middot; ${escapeHtml(formatKm(m.jarak_tempuh))}</td>
//                                     <td data-label="Harga Beli" class="px-4 py-3.5 text-muted tabular-nums">${escapeHtml(m.harga_beli_formatted)}</td>
//                                     <td data-label="Harga Jual" class="px-4 py-3.5 font-bold text-ink tabular-nums">${escapeHtml(m.harga_jual_formatted)}</td>
//                                     <td data-label="Potensi Laba" class="px-4 py-3.5 font-semibold text-emerald-700 tabular-nums">${escapeHtml(m.potensi_keuntungan_formatted)}</td>
//                                     <td data-label="Status" class="px-4 py-3.5">
//                                         <div class="flex flex-wrap items-center gap-2">
//                                             ${statusBadgeHtml(m.status)}
//                                             ${m.status !== 'terjual' ? `<button type="button" data-toggle-status="${m.id}" data-next-status="${nextStatus}" class="text-xs font-semibold text-muted underline hover:text-ink">${nextLabel}</button>` : ''}
//                                         </div>
//                                     </td>
//                                     <td data-label="Aksi" class="px-5 py-3.5 text-right">
//                                         <div class="inline-flex items-center gap-1.5">
//                                             <a href="/motor/${encodeURIComponent(m.id)}" target="_blank" rel="noopener noreferrer" class="rounded border border-line bg-white px-2.5 py-1.5 text-xs font-semibold text-ink hover:bg-surface">Publik</a>
//                                             <a href="/admin/motor/${encodeURIComponent(m.id)}/edit" class="rounded border border-line bg-white px-2.5 py-1.5 text-xs font-semibold text-ink hover:bg-surface">Edit</a>
//                                             <button type="button" data-delete-motor="${m.id}" data-motor-name="${escapeHtml(m.nama_motor)}" class="rounded border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100">Hapus</button>
//                                         </div>
//                                     </td>
//                                 </tr>
//                             `;
//                         }).join('')}
//                     </tbody>
//                 </table>
//             </div>
//         `;
//
//         attachImageFallbacks(tableContainer);
//
//         tableContainer.querySelectorAll('[data-toggle-status]').forEach((btn) => {
//             btn.addEventListener('click', async () => {
//                 const id = btn.getAttribute('data-toggle-status');
//                 const status = btn.getAttribute('data-next-status');
//                 btn.disabled = true;
//                 const updateRes = await apiFetch(`/admin/motor/${encodeURIComponent(id)}`, {
//                     method: 'POST',
//                     body: JSON.stringify({ status }),
//                 });
//                 if (updateRes.ok) {
//                     showFeedback('success', `Status unit berhasil diperbarui menjadi ${status}.`);
//                     await loadMotors(page);
//                 } else {
//                     showFeedback('error', updateRes.message);
//                     btn.disabled = false;
//                 }
//             });
//         });
//
//         tableContainer.querySelectorAll('[data-delete-motor]').forEach((btn) => {
//             btn.addEventListener('click', async () => {
//                 const id = btn.getAttribute('data-delete-motor');
//                 const name = btn.getAttribute('data-motor-name');
//                 if (!window.confirm(`Hapus unit ${name}?`)) {
//                     return;
//                 }
//                 btn.disabled = true;
//                 const delRes = await apiFetch(`/admin/motor/${encodeURIComponent(id)}`, { method: 'DELETE' });
//                 if (delRes.ok) {
//                     showFeedback('success', delRes.message || 'Unit berhasil dihapus.');
//                     await loadMotors(page);
//                 } else {
//                     showFeedback('error', delRes.message);
//                     btn.disabled = false;
//                 }
//             });
//         });
//     }
//
//     filterForm?.addEventListener('submit', (event) => {
//         event.preventDefault();
//         loadMotors(1);
//     });
//
//     document.getElementById('admin-filter-reset')?.addEventListener('click', () => {
//         filterForm?.reset();
//         loadMotors(1);
//     });
//
//     await loadMotors(1);
// }
//
// async function initAdminMotorForm() {
//     const user = await ensureAdminAuthenticated();
//     if (!user) {
//         return;
//     }
//
//     const form = document.getElementById('admin-motor-form');
//     const motorId = form?.getAttribute('data-motor-id') || '';
//     const isEdit = Boolean(motorId);
//     const errorBox = document.getElementById('form-error-summary');
//     const submitBtn = document.getElementById('form-submit-btn');
//
//     // Photo preview
//     const photoInput = document.getElementById('foto_utama');
//     const previewBox = document.getElementById('photo-preview-box');
//     const previewImg = document.getElementById('photo-preview-img');
//     photoInput?.addEventListener('change', () => {
//         const file = photoInput.files?.[0];
//         if (!file || !previewBox || !previewImg) {
//             return;
//         }
//         const reader = new FileReader();
//         reader.onload = (e) => {
//             if (typeof e.target?.result === 'string') {
//                 previewImg.src = e.target.result;
//                 previewBox.hidden = false;
//             }
//         };
//         reader.readAsDataURL(file);
//     });
//
//     if (isEdit && form) {
//         const res = await apiFetch(`/admin/motor/${encodeURIComponent(motorId)}`);
//         if (!res.ok || !res.data) {
//             if (errorBox) {
//                 errorBox.hidden = false;
//                 errorBox.textContent = `Gagal memuat data motor: ${res.message}`;
//             }
//             return;
//         }
//         const m = res.data;
//         const fields = ['nama_motor', 'merk', 'tipe_motor', 'tahun', 'harga_beli', 'harga_jual', 'jarak_tempuh', 'kondisi', 'surat_surat', 'pajak', 'garansi', 'status', 'deskripsi'];
//         fields.forEach((name) => {
//             const el = form.elements.namedItem(name);
//             if (el && m[name] !== null && m[name] !== undefined) {
//                 el.value = String(m[name]);
//             }
//         });
//         if (m.foto_utama && previewBox && previewImg) {
//             previewImg.src = m.foto_utama;
//             previewBox.hidden = false;
//         }
//     }
//
//     form?.addEventListener('submit', async (event) => {
//         event.preventDefault();
//         if (errorBox) {
//             errorBox.hidden = true;
//             errorBox.innerHTML = '';
//         }
//
//         const fd = new FormData(form);
//         // Remove empty file inputs so optional image rules pass cleanly
//         if (!photoInput?.files?.length) {
//             fd.delete('foto_utama');
//         }
//         const galleryInput = document.getElementById('photos');
//         if (!galleryInput?.files?.length) {
//             fd.delete('photos[]');
//         }
//
//         if (submitBtn) {
//             submitBtn.disabled = true;
//             submitBtn.textContent = 'Menyimpan...';
//         }
//
//         const endpoint = isEdit ? `/admin/motor/${encodeURIComponent(motorId)}` : '/admin/motor';
//         const res = await apiFetch(endpoint, {
//             method: 'POST',
//             body: fd,
//         });
//
//         if (!res.ok) {
//             if (errorBox) {
//                 const lines = res.errors
//                     ? Object.values(res.errors).flat().map((msg) => `<li>${escapeHtml(msg)}</li>`).join('')
//                     : `<li>${escapeHtml(res.message)}</li>`;
//                 errorBox.hidden = false;
//                 errorBox.innerHTML = `<p class="font-bold">Periksa kembali data unit:</p><ul class="mt-1 list-disc pl-5">${lines}</ul>`;
//                 errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
//             }
//             if (submitBtn) {
//                 submitBtn.disabled = false;
//                 submitBtn.textContent = isEdit ? 'Simpan Perubahan' : 'Simpan Unit ke Katalog';
//             }
//             return;
//         }
//
//         window.location.href = '/admin/motor';
//     });
// }

document.addEventListener('DOMContentLoaded', () => {
    setupCommonControls();

    const page = document.body.getAttribute('data-page');
    if (page === 'public-home') {
        initPublicHome();
    } else if (page === 'public-catalog') {
        initPublicCatalog();
    } else if (page === 'public-detail') {
        initPublicDetail();
    } else if (page === 'auth-login') {
        initAuthLogin();
    }
    // Bagian Panel Admin (di-comment dulu — diserahkan ke anggota tim berikutnya)
    // else if (page === 'admin-dashboard') {
    //     initAdminDashboard();
    // } else if (page === 'admin-motors-index') {
    //     initAdminMotorsIndex();
    // } else if (page === 'admin-motors-form') {
    //     initAdminMotorForm();
    // }
});
