import {
    apiFetch,
    escapeHtml,
    formatRupiah,
    getToken,
} from './api.js';

function formatTanggalId(dateStr) {
    if (!dateStr) {
        return '-';
    }
    const parsed = new Date(`${dateStr}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) {
        return dateStr;
    }
    return parsed.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

function paymentBadgeHtml(method) {
    const label = String(method || 'Cash');
    const normalized = label.toLowerCase();
    let classes = 'bg-slate-100 text-slate-800 border-slate-200';
    if (normalized.includes('cash') || normalized.includes('tunai')) {
        classes = 'bg-emerald-50 text-emerald-800 border-emerald-200';
    } else if (normalized.includes('transfer')) {
        classes = 'bg-blue-50 text-blue-800 border-blue-200';
    } else if (normalized.includes('kredit')) {
        classes = 'bg-amber-50 text-amber-900 border-amber-200';
    }

    return `<span class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-semibold ${classes}">${escapeHtml(label)}</span>`;
}

function formatProfitClass(amount) {
    return Number(amount) < 0 ? 'text-brand' : 'text-emerald-700';
}

async function downloadAuthenticatedFile(endpoint, fallbackFilename) {
    const token = getToken();
    const response = await fetch(`/api${endpoint}`, {
        method: 'GET',
        headers: {
            Accept: 'application/octet-stream, application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
    });

    if (!response.ok) {
        let message = 'Gagal mengunduh berkas laporan.';
        try {
            const payload = await response.json();
            if (payload?.message) {
                message = payload.message;
            }
        } catch {
            // Ignore non-JSON error body
        }
        throw new Error(message);
    }

    const disposition = response.headers.get('Content-Disposition') || '';
    const match = disposition.match(/filename="?([^";]+)"?/i);
    const filename = match?.[1] || fallbackFilename;

    const blob = await response.blob();
    const objectUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = objectUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(objectUrl);
}

export async function initAdminTransactionsIndex(ensureAdminAuthenticated) {
    const user = await ensureAdminAuthenticated();
    if (!user) {
        return;
    }

    const filterForm = document.getElementById('admin-tx-filter');
    const resetBtn = document.getElementById('tx-filter-reset');
    const container = document.getElementById('admin-tx-table-container');
    const countLabel = document.getElementById('tx-total-count');
    const feedback = document.getElementById('tx-feedback');

    let currentPage = 1;

    function showFeedback(message, isError = false) {
        if (!feedback) {
            return;
        }
        feedback.hidden = false;
        feedback.className = isError
            ? 'mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900'
            : 'mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900';
        feedback.textContent = message;
    }

    async function loadTransactions(page = 1) {
        currentPage = page;
        const params = new URLSearchParams();
        if (filterForm) {
            const fd = new FormData(filterForm);
            for (const [key, value] of fd.entries()) {
                const trimmed = String(value).trim();
                if (trimmed) {
                    params.set(key, trimmed);
                }
            }
        }
        params.set('per_page', '15');
        params.set('page', String(page));

        if (container) {
            container.innerHTML = '<div class="px-6 py-10 text-center text-sm text-muted" role="status">Memuat daftar transaksi penjualan...</div>';
        }

        const res = await apiFetch(`/admin/transaksi?${params.toString()}`);
        if (!res.ok) {
            showFeedback(res.message || 'Gagal memuat riwayat transaksi.', true);
            if (container) {
                container.innerHTML = '<div class="px-6 py-10 text-center text-sm text-red-800">Gagal mengambil data transaksi dari server.</div>';
            }
            return;
        }

        const items = Array.isArray(res.data) ? res.data : [];
        const total = res.meta?.total ?? items.length;
        if (countLabel) {
            countLabel.textContent = `${total} transaksi ditemukan`;
        }

        if (items.length === 0) {
            if (container) {
                container.innerHTML = `
                    <div class="px-6 py-12 text-center">
                        <p class="text-base font-bold text-ink">Belum ada transaksi sesuai filter</p>
                        <p class="mt-1 text-sm text-muted">Catat transaksi penjualan baru atau ubah kriteria pencarian.</p>
                        <div class="mt-5">
                            <a href="/admin/transaksi/create" class="btn-primary text-sm">Catat Transaksi Baru</a>
                        </div>
                    </div>
                `;
            }
            return;
        }

        if (container) {
            container.innerHTML = `
                <div tabindex="0" role="region" aria-label="Tabel riwayat transaksi penjualan" class="table-wrapper">
                    <table class="data-table data-table--stack text-left text-sm">
                        <thead>
                            <tr class="border-b border-line text-xs font-semibold text-muted">
                                <th scope="col" class="px-4 py-3">Kode &amp; Tanggal</th>
                                <th scope="col" class="px-4 py-3">Unit Motor</th>
                                <th scope="col" class="px-4 py-3">Pembeli</th>
                                <th scope="col" class="px-4 py-3">Metode</th>
                                <th scope="col" class="px-4 py-3 text-right">Harga Jual</th>
                                <th scope="col" class="px-4 py-3 text-right">Keuntungan</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            ${items.map((tx) => `
                                <tr class="hover:bg-surface/60">
                                    <th scope="row" data-label="Kode & Tanggal" class="px-4 py-3 font-semibold text-ink">
                                        <span class="block font-mono text-xs text-ink">${escapeHtml(tx.kode_transaksi)}</span>
                                        <span class="block text-xs font-normal text-muted">${escapeHtml(formatTanggalId(tx.tanggal_transaksi))}</span>
                                    </th>
                                    <td data-label="Unit Motor" class="px-4 py-3">
                                        <p class="font-semibold text-ink">${escapeHtml(tx.motor?.nama_motor || '-')}</p>
                                        <p class="text-xs text-muted">${escapeHtml(tx.motor?.merk || '-')} &middot; ${escapeHtml(tx.motor?.tahun || '-')}</p>
                                    </td>
                                    <td data-label="Pembeli" class="px-4 py-3">
                                        <p class="font-medium text-ink">${escapeHtml(tx.nama_pembeli)}</p>
                                        <p class="text-xs text-muted tabular-nums">${escapeHtml(tx.no_telepon_pembeli)}</p>
                                    </td>
                                    <td data-label="Metode" class="px-4 py-3">${paymentBadgeHtml(tx.metode_pembayaran)}</td>
                                    <td data-label="Harga Jual" class="px-4 py-3 text-right font-semibold text-ink tabular-nums">${escapeHtml(tx.harga_jual_formatted || formatRupiah(tx.harga_jual))}</td>
                                    <td data-label="Keuntungan" class="px-4 py-3 text-right font-bold tabular-nums ${formatProfitClass(tx.keuntungan)}">${escapeHtml(tx.keuntungan_formatted || formatRupiah(tx.keuntungan))}</td>
                                    <td data-label="Aksi" class="px-4 py-3 text-right">
                                        <a href="/admin/transaksi/${encodeURIComponent(tx.id)}" class="inline-flex min-h-9 items-center rounded border border-line bg-white px-3 text-xs font-semibold text-ink hover:border-ink">
                                            Detail &amp; Nota
                                        </a>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ${res.meta && res.meta.last_page > 1 ? `
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line px-4 py-3">
                        <p class="text-xs text-muted tabular-nums">Halaman ${res.meta.current_page} dari ${res.meta.last_page}</p>
                        <div class="flex gap-2">
                            <button type="button" data-tx-page="${Math.max(1, res.meta.current_page - 1)}" ${res.meta.current_page <= 1 ? 'disabled' : ''} class="btn-secondary min-h-9 px-3 py-1.5 text-xs">Sebelumnya</button>
                            <button type="button" data-tx-page="${Math.min(res.meta.last_page, res.meta.current_page + 1)}" ${res.meta.current_page >= res.meta.last_page ? 'disabled' : ''} class="btn-secondary min-h-9 px-3 py-1.5 text-xs">Berikutnya</button>
                        </div>
                    </div>
                ` : ''}
            `;

            container.querySelectorAll('[data-tx-page]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    loadTransactions(Number(btn.getAttribute('data-tx-page')));
                });
            });
        }
    }

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        loadTransactions(1);
    });

    resetBtn?.addEventListener('click', () => {
        filterForm?.reset();
        loadTransactions(1);
    });

    await loadTransactions(currentPage);
}

export async function initAdminTransactionCreate(ensureAdminAuthenticated) {
    const user = await ensureAdminAuthenticated();
    if (!user) {
        return;
    }

    const form = document.getElementById('admin-tx-create-form');
    const motorSelect = document.getElementById('tx-motor-id');
    const previewBox = document.getElementById('tx-motor-preview');
    const previewMerk = document.getElementById('tx-preview-merk');
    const previewStatus = document.getElementById('tx-preview-status');
    const previewBeli = document.getElementById('tx-preview-beli');
    const previewJual = document.getElementById('tx-preview-jual');
    const hargaBeliInput = document.getElementById('tx-harga-beli');
    const hargaJualInput = document.getElementById('tx-harga-jual');
    const profitBox = document.getElementById('tx-profit-box');
    const profitValue = document.getElementById('tx-profit-value');
    const errorBox = document.getElementById('tx-form-error');
    const submitBtn = document.getElementById('tx-submit-btn');

    const motorsById = new Map();

    function updateProfitPreview() {
        const beli = Number(hargaBeliInput?.value || 0);
        const jual = Number(hargaJualInput?.value || 0);
        const profit = jual - beli;

        if (profitValue) {
            profitValue.textContent = formatRupiah(profit);
        }
        if (profitBox && profitValue) {
            if (profit < 0) {
                profitBox.className = 'flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3.5';
                profitValue.className = 'text-2xl font-extrabold tracking-tight text-brand tabular-nums';
            } else {
                profitBox.className = 'flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3.5';
                profitValue.className = 'text-2xl font-extrabold tracking-tight text-emerald-800 tabular-nums';
            }
        }
    }

    const res = await apiFetch('/admin/motor?per_page=100');
    const sellableMotors = (res.ok && Array.isArray(res.data) ? res.data : [])
        .filter((m) => m.status !== 'terjual');

    if (motorSelect) {
        if (sellableMotors.length === 0) {
            motorSelect.innerHTML = '<option value="">Tidak ada unit tersedia atau booking saat ini</option>';
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        } else {
            motorSelect.innerHTML = '<option value="">-- Pilih unit motor --</option>' +
                sellableMotors.map((m) => {
                    motorsById.set(String(m.id), m);
                    const statusLabel = m.status === 'booking' ? 'Booking/DP' : 'Tersedia';
                    return `<option value="${escapeHtml(m.id)}">${escapeHtml(m.nama_motor)} (${escapeHtml(m.tahun)}) — ${escapeHtml(statusLabel)} — ${escapeHtml(formatRupiah(m.harga_jual))}</option>`;
                }).join('');
        }
    }

    motorSelect?.addEventListener('change', () => {
        const selected = motorsById.get(motorSelect.value);
        if (!selected) {
            if (previewBox) {
                previewBox.hidden = true;
            }
            return;
        }

        if (previewBox) {
            previewBox.hidden = false;
        }
        if (previewMerk) {
            previewMerk.textContent = `${selected.merk} / ${selected.tipe_motor}`;
        }
        if (previewStatus) {
            previewStatus.textContent = `${selected.tahun} (${selected.status === 'booking' ? 'Booking / DP' : 'Tersedia'})`;
        }
        if (previewBeli) {
            previewBeli.textContent = formatRupiah(selected.harga_beli);
        }
        if (previewJual) {
            previewJual.textContent = formatRupiah(selected.harga_jual);
        }
        if (hargaBeliInput) {
            hargaBeliInput.value = String(selected.harga_beli ?? 0);
        }
        if (hargaJualInput) {
            hargaJualInput.value = String(selected.harga_jual ?? 0);
        }
        updateProfitPreview();
    });

    hargaBeliInput?.addEventListener('input', updateProfitPreview);
    hargaJualInput?.addEventListener('input', updateProfitPreview);

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (errorBox) {
            errorBox.hidden = true;
            errorBox.innerHTML = '';
        }

        const fd = new FormData(form);
        const payload = {
            motor_id: Number(fd.get('motor_id')),
            tanggal_transaksi: String(fd.get('tanggal_transaksi') || '').trim(),
            metode_pembayaran: String(fd.get('metode_pembayaran') || 'Cash').trim(),
            harga_beli: Number(fd.get('harga_beli')),
            harga_jual: Number(fd.get('harga_jual')),
            nama_pembeli: String(fd.get('nama_pembeli') || '').trim(),
            no_telepon_pembeli: String(fd.get('no_telepon_pembeli') || '').trim(),
            alamat_pembeli: String(fd.get('alamat_pembeli') || '').trim() || null,
            catatan: String(fd.get('catatan') || '').trim() || null,
        };

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan transaksi...';
        }

        const saveRes = await apiFetch('/admin/transaksi', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        if (!saveRes.ok || !saveRes.data) {
            if (errorBox) {
                errorBox.hidden = false;
                if (saveRes.errors && typeof saveRes.errors === 'object') {
                    const list = Object.values(saveRes.errors)
                        .flat()
                        .map((msg) => `<li>${escapeHtml(msg)}</li>`)
                        .join('');
                    errorBox.innerHTML = `<p class="font-bold">Periksa kembali data transaksi:</p><ul class="mt-1 list-disc pl-5">${list}</ul>`;
                } else {
                    errorBox.textContent = saveRes.message || 'Gagal mencatat transaksi penjualan.';
                }
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Transaksi & Tandai Terjual';
            }
            return;
        }

        window.location.href = `/admin/transaksi/${encodeURIComponent(saveRes.data.id)}?created=1`;
    });
}

export async function initAdminTransactionShow(ensureAdminAuthenticated, loadKontak) {
    const user = await ensureAdminAuthenticated();
    if (!user) {
        return;
    }

    const root = document.getElementById('tx-detail-root');
    const feedback = document.getElementById('tx-show-feedback');
    const printBtn = document.getElementById('btn-print-invoice');
    const txId = root?.getAttribute('data-transaction-id');

    printBtn?.addEventListener('click', () => {
        window.print();
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('created') === '1' && feedback) {
        feedback.hidden = false;
        feedback.className = 'mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 print:hidden';
        feedback.textContent = 'Transaksi penjualan berhasil dicatat. Status unit motor otomatis diperbarui menjadi Terjual.';
    }

    if (!root || !txId) {
        return;
    }

    const [txRes, kontak] = await Promise.all([
        apiFetch(`/admin/transaksi/${encodeURIComponent(txId)}`),
        loadKontak(),
    ]);

    if (!txRes.ok || !txRes.data) {
        root.innerHTML = `
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-6 text-center text-sm text-red-900">
                <p class="font-bold">Data transaksi tidak ditemukan</p>
                <p class="mt-1">${escapeHtml(txRes.message || 'Periksa kembali nomor transaksi.')}</p>
            </div>
        `;
        return;
    }

    const tx = txRes.data;
    document.title = `Nota ${tx.kode_transaksi} | ${kontak.nama_showroom}`;

    root.innerHTML = `
        <article id="printable-invoice" class="rounded-lg border border-line bg-white p-6 sm:p-8 print:border-0 print:p-0">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-ink pb-5">
                <div>
                    <p class="text-xl font-extrabold tracking-tight text-ink">${escapeHtml(kontak.nama_showroom)}</p>
                    <p class="mt-1 max-w-md text-xs leading-relaxed text-muted">${escapeHtml(kontak.alamat)}</p>
                    <p class="mt-0.5 text-xs text-muted">WhatsApp: ${escapeHtml(kontak.no_whatsapp)}</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="inline-block rounded bg-ink px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-white">Nota Penjualan</span>
                    <p class="mt-2 font-mono text-sm font-bold text-ink">${escapeHtml(tx.kode_transaksi)}</p>
                    <p class="text-xs text-muted">Tanggal: ${escapeHtml(formatTanggalId(tx.tanggal_transaksi))}</p>
                </div>
            </header>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted">Data Pembeli</h2>
                    <dl class="mt-2 space-y-1 text-sm">
                        <div>
                            <dt class="sr-only">Nama</dt>
                            <dd class="font-bold text-ink">${escapeHtml(tx.nama_pembeli)}</dd>
                        </div>
                        <div>
                            <dt class="sr-only">Telepon</dt>
                            <dd class="text-muted tabular-nums">${escapeHtml(tx.no_telepon_pembeli)}</dd>
                        </div>
                        ${tx.alamat_pembeli ? `
                            <div>
                                <dt class="sr-only">Alamat</dt>
                                <dd class="text-muted">${escapeHtml(tx.alamat_pembeli)}</dd>
                            </div>
                        ` : ''}
                    </dl>
                </div>

                <div class="sm:text-right">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-muted">Informasi Pembayaran</h2>
                    <p class="mt-2">${paymentBadgeHtml(tx.metode_pembayaran)}</p>
                    <p class="mt-2 text-xs text-muted">Status Pembayaran: <span class="font-semibold text-emerald-700">Lunas / Tercatat</span></p>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border border-line">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-line bg-surface text-xs font-semibold text-muted">
                        <tr>
                            <th scope="col" class="px-4 py-3">Deskripsi Unit Motor</th>
                            <th scope="col" class="px-4 py-3">Merk / Tipe</th>
                            <th scope="col" class="px-4 py-3 text-center">Tahun</th>
                            <th scope="col" class="px-4 py-3 text-right">Harga Deal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-4 font-bold text-ink">${escapeHtml(tx.motor?.nama_motor || '-')}</td>
                            <td class="px-4 py-4 text-muted">${escapeHtml(tx.motor?.merk || '-')} / ${escapeHtml(tx.motor?.tipe_motor || '-')}</td>
                            <td class="px-4 py-4 text-center tabular-nums text-ink">${escapeHtml(tx.motor?.tahun || '-')}</td>
                            <td class="px-4 py-4 text-right font-extrabold text-ink tabular-nums">${escapeHtml(tx.harga_jual_formatted || formatRupiah(tx.harga_jual))}</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-line bg-surface">
                        <tr>
                            <th scope="row" colspan="3" class="px-4 py-3 text-right text-sm font-bold text-ink">Total Dibayarkan</th>
                            <td class="px-4 py-3 text-right text-base font-extrabold text-ink tabular-nums">${escapeHtml(tx.harga_jual_formatted || formatRupiah(tx.harga_jual))}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            ${tx.catatan ? `
                <div class="mt-5 rounded-md border border-line bg-surface px-4 py-3 text-xs text-ink">
                    <span class="font-bold">Catatan:</span> ${escapeHtml(tx.catatan)}
                </div>
            ` : ''}

            <footer class="mt-10 grid grid-cols-2 gap-8 pt-6 text-center text-xs text-muted">
                <div>
                    <p>Pembeli,</p>
                    <p class="mt-14 border-t border-line pt-2 font-semibold text-ink">${escapeHtml(tx.nama_pembeli)}</p>
                </div>
                <div>
                    <p>Hormat Kami,</p>
                    <p class="mt-14 border-t border-line pt-2 font-semibold text-ink">${escapeHtml(kontak.nama_showroom)}</p>
                </div>
            </footer>
        </article>

        <!-- Ringkasan modal & profit internal — otomatis disembunyikan saat cetak nota pembeli -->
        <section aria-labelledby="internal-profit-heading" class="mt-6 rounded-lg border border-line bg-white p-5 sm:p-6 print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-3">
                <div>
                    <h2 id="internal-profit-heading" class="text-sm font-bold text-ink">Ringkasan Keuangan Internal (Tidak Ikut Tercetak di Nota)</h2>
                    <p class="text-xs text-muted">Snapshot harga modal dan keuntungan tersimpan permanen saat transaksi dicatat.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-md border border-line bg-surface p-4">
                    <p class="text-xs font-semibold text-muted">Harga Beli Modal (Snapshot)</p>
                    <p class="mt-1 text-lg font-extrabold text-ink tabular-nums">${escapeHtml(tx.harga_beli_formatted || formatRupiah(tx.harga_beli))}</p>
                </div>
                <div class="rounded-md border border-line bg-surface p-4">
                    <p class="text-xs font-semibold text-muted">Harga Jual Final</p>
                    <p class="mt-1 text-lg font-extrabold text-ink tabular-nums">${escapeHtml(tx.harga_jual_formatted || formatRupiah(tx.harga_jual))}</p>
                </div>
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold text-emerald-900">Keuntungan Bersih</p>
                    <p class="mt-1 text-lg font-extrabold tabular-nums ${formatProfitClass(tx.keuntungan)}">${escapeHtml(tx.keuntungan_formatted || formatRupiah(tx.keuntungan))}</p>
                </div>
            </div>
        </section>
    `;
}

export async function initAdminReportsIndex(ensureAdminAuthenticated, loadKontak) {
    const user = await ensureAdminAuthenticated();
    if (!user) {
        return;
    }

    const filterForm = document.getElementById('admin-report-filter');
    const resetBtn = document.getElementById('report-filter-reset');
    const root = document.getElementById('report-root');
    const feedback = document.getElementById('report-feedback');
    const printBtn = document.getElementById('btn-report-print');
    const exportExcelBtn = document.getElementById('btn-export-excel');
    const exportCsvBtn = document.getElementById('btn-export-csv');
    const printPeriodEl = document.getElementById('report-print-period');
    const printDateEl = document.getElementById('report-print-date');

    const kontak = await loadKontak();
    document.querySelectorAll('[data-report-store-name]').forEach((el) => {
        el.textContent = String(kontak.nama_showroom || 'Andara Motor').toUpperCase();
    });
    document.querySelectorAll('[data-report-store-address]').forEach((el) => {
        el.textContent = kontak.alamat || '';
    });
    document.querySelectorAll('[data-report-store-wa]').forEach((el) => {
        el.textContent = kontak.no_whatsapp ? `WhatsApp: ${kontak.no_whatsapp}` : '';
    });

    function showFeedback(message, isError = false) {
        if (!feedback) {
            return;
        }
        feedback.hidden = false;
        feedback.className = isError
            ? 'mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 print:hidden'
            : 'mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 print:hidden';
        feedback.textContent = message;
    }

    function clearFeedback() {
        if (feedback) {
            feedback.hidden = true;
            feedback.textContent = '';
        }
    }

    function buildFilterParams() {
        const params = new URLSearchParams();
        if (filterForm) {
            const fd = new FormData(filterForm);
            for (const [key, val] of fd.entries()) {
                const trimmed = String(val).trim();
                if (trimmed) {
                    params.set(key, trimmed);
                }
            }
        }
        return params;
    }

    function describePeriod(params) {
        const parts = [];
        const start = params.get('tanggal_mulai');
        const end = params.get('tanggal_selesai');
        const year = params.get('tahun');
        const method = params.get('metode_pembayaran');

        if (start && end) {
            parts.push(`${formatTanggalId(start)} s.d. ${formatTanggalId(end)}`);
        } else if (start) {
            parts.push(`Mulai ${formatTanggalId(start)}`);
        } else if (end) {
            parts.push(`Sampai ${formatTanggalId(end)}`);
        } else if (year) {
            parts.push(`Tahun ${year}`);
        } else {
            parts.push('Semua Periode');
        }

        if (method) {
            parts.push(`Metode: ${method}`);
        }

        return parts.join(' • ');
    }

    async function loadReport() {
        clearFeedback();
        const params = buildFilterParams();
        const qs = params.toString();

        if (printPeriodEl) {
            printPeriodEl.textContent = `Periode: ${describePeriod(params)}`;
        }
        if (printDateEl) {
            printDateEl.textContent = `Dicetak: ${new Date().toLocaleString('id-ID')}`;
        }

        if (root) {
            root.innerHTML = '<div class="rounded-lg border border-line bg-white px-6 py-12 text-center text-sm font-medium text-muted" role="status">Menghitung rekap keuangan showroom...</div>';
        }

        const listParams = new URLSearchParams(params);
        listParams.set('per_page', '100');

        const [rekapRes, txRes] = await Promise.all([
            apiFetch(`/admin/transaksi/rekap${qs ? `?${qs}` : ''}`),
            apiFetch(`/admin/transaksi?${listParams.toString()}`),
        ]);

        if (!rekapRes.ok || !rekapRes.data) {
            showFeedback(rekapRes.message || 'Gagal memuat laporan keuangan.', true);
            if (root) {
                root.innerHTML = '<div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-6 text-center text-sm text-red-900">Tidak dapat mengambil data rekap dari API.</div>';
            }
            return;
        }

        const rekap = rekapRes.data;
        const monthly = Array.isArray(rekap.breakdown_bulanan) ? rekap.breakdown_bulanan : [];
        const transactions = txRes.ok && Array.isArray(txRes.data) ? txRes.data : [];
        const maxMonthlyProfit = Math.max(1, ...monthly.map((m) => Math.abs(Number(m.total_keuntungan || 0))));

        const sumBeli = transactions.reduce((acc, t) => acc + Number(t.harga_beli || 0), 0);
        const sumJual = transactions.reduce((acc, t) => acc + Number(t.harga_jual || 0), 0);
        const sumProfit = transactions.reduce((acc, t) => acc + Number(t.keuntungan || 0), 0);

        if (root) {
            root.innerHTML = `
                <section aria-labelledby="report-kpi-heading">
                    <h2 id="report-kpi-heading" class="text-base font-bold text-ink">Ringkasan Keuangan (${escapeHtml(describePeriod(params))})</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-lg border border-line bg-white p-4">
                            <p class="text-xs font-semibold text-muted">Total Unit Terjual</p>
                            <p class="mt-1.5 text-2xl font-extrabold text-ink tabular-nums">${escapeHtml(rekap.total_unit_terjual)}</p>
                            <p class="mt-1 text-xs text-muted">Unit tercatat</p>
                        </div>
                        <div class="rounded-lg border border-line bg-white p-4">
                            <p class="text-xs font-semibold text-muted">Total Pendapatan (Omset)</p>
                            <p class="mt-1.5 text-lg font-extrabold text-ink tabular-nums">${escapeHtml(rekap.total_omset_formatted || formatRupiah(rekap.total_omset))}</p>
                            <p class="mt-1 text-xs text-muted">Harga jual final</p>
                        </div>
                        <div class="rounded-lg border border-line bg-white p-4">
                            <p class="text-xs font-semibold text-muted">Total Modal Beli</p>
                            <p class="mt-1.5 text-lg font-extrabold text-ink tabular-nums">${escapeHtml(rekap.total_modal_formatted || formatRupiah(rekap.total_modal))}</p>
                            <p class="mt-1 text-xs text-muted">Snapshot harga beli</p>
                        </div>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 p-4">
                            <p class="text-xs font-semibold text-emerald-900">Total Keuntungan Bersih</p>
                            <p class="mt-1.5 text-lg font-extrabold tabular-nums ${formatProfitClass(rekap.total_keuntungan)}">${escapeHtml(rekap.total_keuntungan_formatted || formatRupiah(rekap.total_keuntungan))}</p>
                            <p class="mt-1 text-xs text-emerald-800">Omset &minus; Modal</p>
                        </div>
                        <div class="rounded-lg border border-line bg-white p-4">
                            <p class="text-xs font-semibold text-muted">Rata-rata Laba / Unit</p>
                            <p class="mt-1.5 text-lg font-extrabold text-ink tabular-nums">${escapeHtml(rekap.rata_rata_keuntungan_formatted || formatRupiah(rekap.rata_rata_keuntungan ?? 0))}</p>
                            <p class="mt-1 text-xs text-muted">Margin per penjualan</p>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="monthly-recap-heading" class="mt-8 rounded-lg border border-line bg-white">
                    <div class="border-b border-line px-5 py-4">
                        <h2 id="monthly-recap-heading" class="text-base font-bold text-ink">Rekap Penjualan Bulanan</h2>
                        <p class="text-xs text-muted">Ringkasan jumlah unit, omset, modal, dan keuntungan tiap bulan.</p>
                    </div>

                    ${monthly.length === 0 ? `
                        <p class="px-5 py-8 text-center text-sm text-muted">Belum ada data penjualan bulanan untuk periode ini.</p>
                    ` : `
                        <div tabindex="0" role="region" aria-labelledby="monthly-recap-heading" class="table-wrapper">
                            <table class="data-table data-table--stack text-left text-sm">
                                <thead>
                                    <tr class="border-b border-line text-xs font-semibold text-muted">
                                        <th scope="col" class="px-4 py-3">Bulan</th>
                                        <th scope="col" class="px-4 py-3 text-center">Unit</th>
                                        <th scope="col" class="px-4 py-3 text-right">Omset</th>
                                        <th scope="col" class="px-4 py-3 text-right">Modal</th>
                                        <th scope="col" class="px-4 py-3 text-right">Keuntungan</th>
                                        <th scope="col" class="hidden w-40 px-4 py-3 md:table-cell print:hidden">Proporsi Laba</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    ${monthly.map((row) => {
                                        const pct = Math.min(100, Math.round((Math.abs(Number(row.total_keuntungan || 0)) / maxMonthlyProfit) * 100));
                                        return `
                                            <tr>
                                                <th scope="row" data-label="Bulan" class="px-4 py-3 font-semibold text-ink">${escapeHtml(row.nama_bulan)}</th>
                                                <td data-label="Unit" class="px-4 py-3 text-center font-semibold text-ink tabular-nums">${escapeHtml(row.total_unit)}</td>
                                                <td data-label="Omset" class="px-4 py-3 text-right tabular-nums text-ink">${escapeHtml(formatRupiah(row.total_omset))}</td>
                                                <td data-label="Modal" class="px-4 py-3 text-right tabular-nums text-muted">${escapeHtml(formatRupiah(row.total_modal))}</td>
                                                <td data-label="Keuntungan" class="px-4 py-3 text-right font-bold tabular-nums ${formatProfitClass(row.total_keuntungan)}">${escapeHtml(formatRupiah(row.total_keuntungan))}</td>
                                                <td class="hidden px-4 py-3 md:table-cell print:hidden">
                                                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface">
                                                        <div class="h-full rounded-full ${Number(row.total_keuntungan) < 0 ? 'bg-brand' : 'bg-emerald-600'}" style="width: ${pct}%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    `}
                </section>

                <section aria-labelledby="detail-report-heading" class="mt-8 rounded-lg border border-line bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-4">
                        <div>
                            <h2 id="detail-report-heading" class="text-base font-bold text-ink">Rincian Transaksi Penjualan</h2>
                            <p class="text-xs text-muted">Menampilkan ${transactions.length} transaksi sesuai filter aktif.</p>
                        </div>
                        <a href="/admin/transaksi/create" class="text-xs font-semibold text-brand hover:underline print:hidden">+ Catat Transaksi Baru</a>
                    </div>

                    ${transactions.length === 0 ? `
                        <p class="px-5 py-8 text-center text-sm text-muted">Tidak ada transaksi penjualan pada periode yang dipilih.</p>
                    ` : `
                        <div tabindex="0" role="region" aria-labelledby="detail-report-heading" class="table-wrapper">
                            <table class="data-table data-table--stack text-left text-sm">
                                <thead>
                                    <tr class="border-b border-line text-xs font-semibold text-muted">
                                        <th scope="col" class="px-4 py-3">Tanggal</th>
                                        <th scope="col" class="px-4 py-3">Kode</th>
                                        <th scope="col" class="px-4 py-3">Tipe Motor &amp; Tahun</th>
                                        <th scope="col" class="px-4 py-3">Pembeli</th>
                                        <th scope="col" class="px-4 py-3 text-right">Harga Beli</th>
                                        <th scope="col" class="px-4 py-3 text-right">Harga Jual</th>
                                        <th scope="col" class="px-4 py-3 text-right">Keuntungan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    ${transactions.map((t) => `
                                        <tr>
                                            <td data-label="Tanggal" class="px-4 py-3 whitespace-nowrap text-muted tabular-nums">${escapeHtml(t.tanggal_transaksi)}</td>
                                            <th scope="row" data-label="Kode" class="px-4 py-3 font-mono text-xs font-semibold text-ink">
                                                <a href="/admin/transaksi/${encodeURIComponent(t.id)}" class="hover:text-brand hover:underline">${escapeHtml(t.kode_transaksi)}</a>
                                            </th>
                                            <td data-label="Motor" class="px-4 py-3">
                                                <span class="font-semibold text-ink">${escapeHtml(t.motor?.nama_motor || '-')}</span>
                                                <span class="block text-xs text-muted">${escapeHtml(t.motor?.tipe_motor || '-')} &middot; ${escapeHtml(t.motor?.tahun || '-')}</span>
                                            </td>
                                            <td data-label="Pembeli" class="px-4 py-3 text-ink">${escapeHtml(t.nama_pembeli)}</td>
                                            <td data-label="Harga Beli" class="px-4 py-3 text-right tabular-nums text-muted">${escapeHtml(formatRupiah(t.harga_beli))}</td>
                                            <td data-label="Harga Jual" class="px-4 py-3 text-right font-semibold tabular-nums text-ink">${escapeHtml(formatRupiah(t.harga_jual))}</td>
                                            <td data-label="Keuntungan" class="px-4 py-3 text-right font-bold tabular-nums ${formatProfitClass(t.keuntungan)}">${escapeHtml(formatRupiah(t.keuntungan))}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot class="border-t-2 border-line bg-surface font-bold">
                                    <tr>
                                        <th scope="row" colspan="4" class="px-4 py-3 text-right text-sm text-ink">TOTAL PERIODE INI</th>
                                        <td class="px-4 py-3 text-right tabular-nums text-ink">${escapeHtml(formatRupiah(sumBeli))}</td>
                                        <td class="px-4 py-3 text-right tabular-nums text-ink">${escapeHtml(formatRupiah(sumJual))}</td>
                                        <td class="px-4 py-3 text-right tabular-nums ${formatProfitClass(sumProfit)}">${escapeHtml(formatRupiah(sumProfit))}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `}
                </section>
            `;
        }
    }

    document.querySelectorAll('[data-report-preset]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const preset = btn.getAttribute('data-report-preset');
            const startInput = document.getElementById('report-start');
            const endInput = document.getElementById('report-end');
            const yearSelect = document.getElementById('report-year');
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const lastDay = String(new Date(yyyy, now.getMonth() + 1, 0).getDate()).padStart(2, '0');

            if (preset === 'this-month') {
                if (startInput) {
                    startInput.value = `${yyyy}-${mm}-01`;
                }
                if (endInput) {
                    endInput.value = `${yyyy}-${mm}-${lastDay}`;
                }
                if (yearSelect) {
                    yearSelect.value = '';
                }
            } else if (preset === 'this-year') {
                if (startInput) {
                    startInput.value = '';
                }
                if (endInput) {
                    endInput.value = '';
                }
                if (yearSelect) {
                    yearSelect.value = String(yyyy);
                }
            } else {
                filterForm?.reset();
            }

            loadReport();
        });
    });

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        loadReport();
    });

    resetBtn?.addEventListener('click', () => {
        filterForm?.reset();
        loadReport();
    });

    printBtn?.addEventListener('click', () => {
        window.print();
    });

    async function handleExport(button, type) {
        if (!button) {
            return;
        }
        clearFeedback();
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Menyiapkan file...';

        try {
            const qs = buildFilterParams().toString();
            const endpoint = `/admin/transaksi/export/${type}${qs ? `?${qs}` : ''}`;
            const ext = type === 'excel' ? 'xlsx' : 'csv';
            await downloadAuthenticatedFile(endpoint, `laporan_keuangan_andara.${ext}`);
            showFeedback(`Berkas laporan (${ext.toUpperCase()}) berhasil diunduh.`);
        } catch (err) {
            showFeedback(err.message || 'Gagal mengunduh berkas laporan.', true);
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    exportExcelBtn?.addEventListener('click', () => handleExport(exportExcelBtn, 'excel'));
    exportCsvBtn?.addEventListener('click', () => handleExport(exportCsvBtn, 'csv'));

    await loadReport();
}