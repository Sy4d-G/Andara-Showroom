const TOKEN_KEY = 'andara_admin_token';
const USER_KEY = 'andara_admin_user';

const STANDIN_PHOTOS = [
    '/images/motorcycles/photo-f44715247-960.jpg',
    '/images/motorcycles/photo-f105187321-960.jpg',
    '/images/motorcycles/photo-f130098625-500.jpg',
    '/images/motorcycles/photo-f152528080-960.jpg',
    '/images/motorcycles/photo-f193629767-960.jpg',
];

export function getToken() {
    return window.localStorage.getItem(TOKEN_KEY);
}

export function setAuth(token, user) {
    window.localStorage.setItem(TOKEN_KEY, token);
    window.localStorage.setItem(USER_KEY, JSON.stringify(user ?? null));
}

export function clearAuth() {
    window.localStorage.removeItem(TOKEN_KEY);
    window.localStorage.removeItem(USER_KEY);
}

export function getStoredUser() {
    try {
        const raw = window.localStorage.getItem(USER_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export async function apiFetch(path, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');

    const token = getToken();
    if (token && !headers.has('Authorization')) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }

    try {
        const response = await fetch(`/api${path}`, {
            ...options,
            headers,
        });

        let payload = null;
        try {
            payload = await response.json();
        } catch {
            payload = null;
        }

        if (response.status === 401 && window.location.pathname.startsWith('/admin')) {
            clearAuth();
            window.location.href = '/login';
        }

        const fallbackMsg = response.status >= 500
            ? 'Server sedang mengalami gangguan. Pastikan database aktif lalu coba muat ulang.'
            : 'Terjadi kesalahan saat memuat data.';

        return {
            ok: response.ok,
            status: response.status,
            data: payload?.data ?? null,
            meta: payload?.meta ?? null,
            message: response.ok ? (payload?.message ?? '') : (response.status >= 500 ? fallbackMsg : (payload?.message ?? fallbackMsg)),
            errors: payload?.errors ?? null,
            raw: payload,
        };
    } catch {
        return {
            ok: false,
            status: 0,
            data: null,
            meta: null,
            message: 'Tidak dapat terhubung ke server API. Periksa koneksi atau server lokal Anda.',
            errors: null,
            raw: null,
        };
    }
}

export function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

export function formatRupiah(amount) {
    const number = Number(amount ?? 0);
    return `Rp ${number.toLocaleString('id-ID')}`;
}

export function formatKm(km) {
    const number = Number(km ?? 0);
    return `${number.toLocaleString('id-ID')} km`;
}

export function statusBadgeHtml(status) {
    const map = {
        tersedia: {
            label: 'Tersedia',
            classes: 'bg-emerald-50 text-emerald-800 border-emerald-200',
            dot: 'bg-emerald-600',
        },
        booking: {
            label: 'Dipesan',
            classes: 'bg-amber-50 text-amber-900 border-amber-200',
            dot: 'bg-amber-500',
        },
        terjual: {
            label: 'Terjual',
            classes: 'bg-red-50 text-red-800 border-red-200',
            dot: 'bg-red-600',
        },
    };

    const item = map[status] || {
        label: escapeHtml(status || 'Tersedia'),
        classes: 'bg-slate-100 text-slate-800 border-slate-200',
        dot: 'bg-slate-500',
    };

    return `<span class="inline-flex items-center gap-1.5 rounded border px-2.5 py-0.5 text-xs font-semibold tracking-tight ${item.classes}">
        <span class="h-1.5 w-1.5 rounded-full ${item.dot}" aria-hidden="true"></span>
        ${item.label}
    </span>`;
}

export function getStandinPhoto(id = 0) {
    const index = Math.abs(Number(id) || 0) % STANDIN_PHOTOS.length;
    return STANDIN_PHOTOS[index];
}

export function getMotorPhotoUrl(motor) {
    if (motor?.foto_utama) {
        return motor.foto_utama;
    }
    return getStandinPhoto(motor?.id ?? 0);
}

const DEFAULT_WA_NUMBER = '6282298213826';
const LEGACY_SEED_WA = '6281234567890';

export function buildMotorWhatsappUrl(motor, kontak) {
    const rawPhone = String(kontak?.no_whatsapp || DEFAULT_WA_NUMBER).replace(/\D+/g, '');
    const phone = !rawPhone || rawPhone === LEGACY_SEED_WA ? DEFAULT_WA_NUMBER : rawPhone;

    if (motor?.whatsapp_url) {
        return String(motor.whatsapp_url).replace(`wa.me/${LEGACY_SEED_WA}`, `wa.me/${DEFAULT_WA_NUMBER}`);
    }

    const storeName = kontak?.nama_showroom || 'Andara Motor';
    const message = `Halo ${storeName}, saya tertarik dengan ${motor.nama_motor} (${motor.tahun}) seharga ${formatRupiah(motor.harga_jual)}. Apakah unit masih tersedia?`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
}

export function renderMotorCard(motor, kontak) {
    const photoUrl = escapeHtml(getMotorPhotoUrl(motor));
    const fallbackUrl = escapeHtml(getStandinPhoto(motor.id));
    const waUrl = escapeHtml(buildMotorWhatsappUrl(motor, kontak));
    const detailUrl = `/motor/${encodeURIComponent(motor.id)}`;

    return `
        <article class="unit-card flex h-full flex-col overflow-hidden rounded-lg border border-line bg-white">
            <a href="${detailUrl}" class="relative block aspect-[4/3] w-full overflow-hidden bg-surface">
                <img
                    src="${photoUrl}"
                    alt="${escapeHtml(motor.nama_motor)} tahun ${escapeHtml(motor.tahun)}"
                    width="640"
                    height="480"
                    loading="lazy"
                    decoding="async"
                    data-fallback="${fallbackUrl}"
                    class="unit-photo h-full w-full object-cover"
                >
                <div class="absolute left-3 top-3">
                    ${statusBadgeHtml(motor.status)}
                </div>
            </a>

            <div class="flex flex-1 flex-col p-4">
                <div class="mb-1 flex items-center justify-between gap-2 text-xs font-medium text-muted">
                    <span>${escapeHtml(motor.merk)}</span>
                    <span>${escapeHtml(motor.tipe_motor)}</span>
                </div>

                <h3 class="text-base font-bold leading-snug text-ink">
                    <a href="${detailUrl}" class="hover:text-brand">
                        ${escapeHtml(motor.nama_motor)}
                    </a>
                </h3>

                <p class="mt-1.5 text-sm text-muted tabular-nums">
                    ${escapeHtml(motor.tahun)} &middot; ${escapeHtml(formatKm(motor.jarak_tempuh))}
                </p>

                <div class="mt-3 flex flex-wrap items-baseline justify-between gap-2 border-t border-line pt-3">
                    <p class="text-lg font-extrabold tracking-tight text-ink tabular-nums">
                        ${escapeHtml(formatRupiah(motor.harga_jual))}
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 pt-1">
                    <a href="${detailUrl}" class="btn-secondary px-3 text-sm">
                        Lihat Detail
                    </a>
                    <a href="${waUrl}" target="_blank" rel="noopener noreferrer" class="btn-primary px-3 text-sm">
                        Tanya WA
                    </a>
                </div>
            </div>
        </article>
    `;
}

export function attachImageFallbacks(container = document) {
    container.querySelectorAll('img[data-fallback]').forEach((img) => {
        if (img.dataset.fallbackBound === '1') {
            return;
        }
        img.dataset.fallbackBound = '1';
        img.addEventListener('error', () => {
            const fallback = img.getAttribute('data-fallback');
            if (fallback && img.getAttribute('src') !== fallback) {
                img.src = fallback;
            }
        });
    });
}
