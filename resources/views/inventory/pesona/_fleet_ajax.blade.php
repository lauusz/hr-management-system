<style>
    .fleet-busy { opacity: .55; transition: opacity .12s; }
    .fleet-status { min-height: 18px; font-size: 12.5px; color: var(--pesona-muted); }
    .fleet-error { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    /* Modal posisi ban: <dialog> native (fokus terkunci, Escape, latar belakang tidak aktif). Satu modal untuk semua posisi. */
    dialog.fleet-dialog { padding: 0; border: 0; border-radius: 16px; width: min(560px, calc(100vw - 24px)); max-width: none; max-height: calc(100dvh - 24px); color: var(--pesona-text, #111827); box-shadow: 0 8px 32px rgba(0, 0, 0, .18); }
    dialog.fleet-dialog[open] { display: block; }
    dialog.fleet-dialog::backdrop { background: rgba(0, 0, 0, .4); backdrop-filter: blur(2px); }
    .fleet-dialog-card { display: flex; flex-direction: column; max-height: calc(100dvh - 24px); }
    .fleet-dialog-head { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--pesona-border, #E5E7EB); }
    .fleet-dialog-head h2 { margin: 0; flex: 1; min-width: 0; font-size: 16px; font-weight: 800; line-height: 1.3; overflow-wrap: anywhere; }
    .fleet-dialog-body { padding: 14px 16px 18px; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
    .fleet-dialog-body .tire-dl { grid-template-columns: 1fr 1fr; }
    .fleet-dialog-body .tire-grid { grid-template-columns: 1fr; }
    .fleet-dialog-body .req-summary { padding: 10px 12px; border-radius: 10px; background: var(--pesona-primary-softer); border: 1px solid var(--pesona-border); font-size: 13px; }
    .fleet-dialog-body .req-need { font-weight: 800; font-size: 15px; }
</style>
<script>
    // Pembantu AJAX bersama untuk Ban Armada (admin), Ajukan Ban, dan daftar pengajuan. Dimuat satu kali oleh halaman;
    // tidak ada fallback reload otomatis saat gagal: pengguna memilih "Coba Lagi" atau memuat ulang sendiri.
    window.PesonaFleet = (() => {
        const messages = {
            network: 'Gagal memuat data. Periksa koneksi, lalu coba lagi.',
            session: 'Sesi Anda berakhir atau akses ditolak. Muat ulang halaman atau login kembali.',
            forbidden: 'Anda tidak memiliki akses untuk data ini.',
            unavailable: 'Fitur ini belum tersedia. Hubungi administrator.',
            server: 'Terjadi kesalahan saat memuat data. Coba lagi.',
        };

        // Mengembalikan teks partial; melempar {kind} untuk kegagalan, atau AbortError bila dibatalkan.
        async function get(url, expect, signal) {
            const u = new URL(url, location.href);
            u.searchParams.set('partial', expect);
            let res;
            try {
                res = await fetch(u, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin', signal });
            } catch (error) {
                if (error.name === 'AbortError') throw error;
                throw { kind: 'network' };
            }
            // Redirect (mis. ke halaman login) atau 401/419: jangan disisipkan ke panel.
            if (res.redirected || res.status === 401 || res.status === 419) throw { kind: 'session' };
            if (res.status === 403) throw { kind: 'forbidden' };
            if (res.status === 503) throw { kind: 'unavailable' };
            if (!res.ok || res.headers.get('X-Fleet-Partial') !== expect) throw { kind: 'server' };
            return res.text();
        }

        function errorBox(kind) {
            const box = document.createElement('div');
            box.className = 'pesona-alert pesona-alert-error fleet-error';
            box.setAttribute('role', 'alert');
            const text = document.createElement('span');
            text.textContent = messages[kind] || messages.server;
            box.appendChild(text);
            if (kind === 'session') {
                const link = document.createElement('a');
                link.className = 'tire-btn';
                link.href = location.href;
                link.textContent = 'Muat ulang halaman';
                box.appendChild(link);
            } else if (kind !== 'forbidden') {
                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'tire-btn';
                retry.setAttribute('data-retry', '');
                retry.textContent = 'Coba Lagi';
                box.appendChild(retry);
            }
            return box;
        }

        // Blok interaksi selama memuat agar form tidak dikirim dengan konteks lama.
        function busy(el, on) {
            el.inert = on;
            el.classList.toggle('fleet-busy', on);
            if (on) el.setAttribute('aria-busy', 'true'); else el.removeAttribute('aria-busy');
        }

        // Pengendali modal tunggal. Konteks (kendaraan|posisi) menjadi kunci draf input: draf hanya dipulihkan untuk konteks yang sama.
        function createModal(dialog, hooks = {}) {
            const body = dialog.querySelector('[data-modal-body]');
            const title = dialog.querySelector('[data-modal-title]');
            const drafts = {};
            let key = null;
            let silent = false;
            // Field hidden hanya ikut draf bila ditandai data-draft-keep (ID pilihan combobox, disimpan bersama labelnya).
            const draftFields = (form) => form.querySelectorAll('input:not([type=hidden]):not([type=submit]):not([type=button]), input[type=hidden][data-draft-keep], select, textarea');

            function saveDraft() {
                const forms = body.querySelectorAll('form[data-draft]');
                if (!key || !forms.length) return;
                const saved = {};
                forms.forEach((form) => {
                    const values = {};
                    draftFields(form).forEach((el) => { if (el.name) values[el.name] = el.value; });
                    saved[form.dataset.draft] = values;
                });
                drafts[key] = saved;
            }
            function restoreDraft() {
                const saved = key && drafts[key];
                if (!saved) return;
                body.querySelectorAll('form[data-draft]').forEach((form) => {
                    const values = saved[form.dataset.draft];
                    if (!values) return;
                    let touched = false;
                    draftFields(form).forEach((el) => {
                        if (el.name && values[el.name] !== undefined && values[el.name] !== '') { el.value = values[el.name]; touched = true; }
                    });
                    if (touched) form.closest('details')?.setAttribute('open', '');
                    // ID dan label combobox harus sepasang: tanpa label atau tanpa ID, pilihan tidak dipulihkan.
                    form.querySelectorAll('[data-ready-combo]').forEach((box) => {
                        const id = box.querySelector('[data-rc-id]');
                        const label = box.querySelector('[data-rc-input]');
                        if (id.value && !label.value) id.value = '';
                        if (id.value) id.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });
            }
            const readTitle = () => {
                const holder = body.querySelector('[data-title]');
                title.textContent = holder ? holder.dataset.title : '';
                holder?.remove();
            };
            const lock = (on) => { document.body.style.overflow = on ? 'hidden' : ''; };

            dialog.addEventListener('close', () => {
                if (dialog.open) return; // dibuka kembali sebelum event ini diproses
                saveDraft();
                body.replaceChildren(); // satu-satunya salinan form dibuang; draf disimpan per konteks
                title.textContent = '';
                lock(false);
                const quiet = silent; silent = false;
                if (!quiet && hooks.onClose) hooks.onClose();
            });
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog || event.target.closest('[data-modal-close]')) dialog.close();
            });

            return {
                // Konteks baru: simpan draf konteks lama, kosongkan isi (tidak ada info lama), tampilkan loading, buka modal.
                begin(newKey) {
                    saveDraft();
                    key = newKey;
                    const loading = document.createElement('p');
                    loading.className = 'tire-hint';
                    loading.textContent = 'Memuat informasi posisi…';
                    body.replaceChildren(loading);
                    body.setAttribute('aria-busy', 'true');
                    title.textContent = 'Memuat…';
                    if (!dialog.open) { dialog.showModal(); lock(true); }
                },
                show(html) {
                    body.innerHTML = html;
                    body.removeAttribute('aria-busy');
                    readTitle();
                    restoreDraft();
                },
                error(kind) {
                    body.replaceChildren(errorBox(kind));
                    body.removeAttribute('aria-busy');
                    title.textContent = 'Gagal memuat';
                },
                // Dialog yang dirender terbuka oleh server (tautan langsung / validasi gagal) dijadikan modal sungguhan.
                adopt(newKey) {
                    if (!dialog.hasAttribute('open')) return;
                    key = newKey;
                    readTitle();
                    dialog.removeAttribute('open');
                    dialog.showModal();
                    lock(true);
                },
                close(quiet) {
                    if (dialog.open) { silent = !!quiet; dialog.close(); }
                },
                isOpen: () => dialog.open,
            };
        }

        return { get, errorBox, busy, createModal };
    })();
</script>
