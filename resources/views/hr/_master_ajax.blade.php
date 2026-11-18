{{-- Loader & pemuat hasil bersama untuk tiga halaman master HR (absensi, izin/cuti, lembur).
     Tanpa JavaScript, form GET dan tautan pagination biasa tetap berfungsi. --}}
@push('styles')
<style>
    .mx-primary { position: relative; }
    .mx-loading { pointer-events: none; }
    .mx-primary.mx-loading { min-height: 140px; }
    .mx-loading:not(.mx-primary) { opacity: .55; }
    .mx-loader {
        position: absolute; inset: 0; z-index: 5; display: flex; align-items: flex-start; justify-content: center;
        padding-top: 56px; background: rgba(255, 255, 255, .72); border-radius: inherit;
    }
    .mx-loader__box {
        display: inline-flex; align-items: center; gap: 10px; padding: 10px 16px; border-radius: 999px;
        background: #fff; box-shadow: 0 4px 16px rgba(15, 23, 42, .12); color: #1f2937; font-size: 13.5px; font-weight: 600;
    }
    .mx-spinner {
        width: 18px; height: 18px; border: 2.5px solid rgba(10, 61, 98, .2); border-top-color: #0A3D62;
        border-radius: 50%; animation: mx-spin .8s linear infinite;
    }
    @keyframes mx-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .mx-spinner { animation: none; } }
    .mx-sr-only { position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .mx-error { margin: 0 0 12px; padding: 10px 14px; border-radius: 10px; background: rgba(239, 68, 68, .1); color: #b91c1c; font-size: 13.5px; font-weight: 600; }
    .mx-error[hidden],
    .attendance-btn-reset[hidden], .attendance-filter-badge[hidden],
    .lm-btn-reset[hidden], .lm-filter-badge[hidden] { display: none !important; }
</style>
@endpush

<script>
    (function () {
        if (window.MasterAjax) return;

        // cfg: form, search, regions[0] = area hasil utama (diberi spinner), links (pagination), reset, sync[], onUpdate(doc)
        window.MasterAjax = {
            init(cfg) {
                const form = document.querySelector(cfg.form);
                const search = cfg.search ? document.querySelector(cfg.search) : null;
                const primary = document.querySelector(cfg.regions[0]);
                if (!form || cfg.regions.some((selector) => !document.querySelector(selector))) return null;

                const status = document.createElement('div');
                status.className = 'mx-sr-only';
                status.setAttribute('role', 'status');
                const errorBox = document.createElement('div');
                errorBox.className = 'mx-error';
                errorBox.setAttribute('role', 'alert');
                errorBox.hidden = true;
                errorBox.textContent = 'Data gagal dimuat. Silakan coba lagi.';
                primary.before(status, errorBox);

                let controller = null;
                let token = 0;
                let timer = null;

                const regionElements = () => cfg.regions.map((selector) => document.querySelector(selector));

                function setBusy(on) {
                    const hadFocus = on && regionElements().some((el) => el.contains(document.activeElement));
                    regionElements().forEach((el, index) => {
                        el.classList.toggle('mx-loading', on);
                        el.classList.toggle('mx-primary', index === 0);
                        el.inert = on;
                        if (index === 0) el.setAttribute('aria-busy', String(on));
                    });
                    primary.querySelector(':scope > .mx-loader')?.remove();
                    status.textContent = on ? 'Memuat data…' : '';
                    if (on) {
                        const loader = document.createElement('div');
                        loader.className = 'mx-loader';
                        loader.setAttribute('aria-hidden', 'true');
                        loader.innerHTML = '<span class="mx-loader__box"><span class="mx-spinner"></span><span>Memuat data…</span></span>';
                        primary.append(loader);
                        setBusy.hadFocus = hadFocus;
                    } else if (setBusy.hadFocus) {
                        // Fokus yang hilang karena area ditutup dikembalikan ke area hasil, bukan ke spinner.
                        primary.tabIndex = -1;
                        primary.focus({ preventScroll: true });
                        setBusy.hadFocus = false;
                    }
                }

                function syncElement(selector, doc) {
                    const current = document.querySelector(selector);
                    const next = doc.querySelector(selector);
                    if (!current || !next) return;
                    current.hidden = next.hidden;
                    if (next.hasAttribute('href')) current.setAttribute('href', next.getAttribute('href'));
                    if (!next.childElementCount) current.textContent = next.textContent;
                }

                // Hanya dipakai saat Reset: menyalin nilai default dari server ke kontrol filter.
                function syncForm(doc) {
                    const nextForm = doc.querySelector(cfg.form);
                    if (!nextForm) return;
                    Array.from(form.elements).forEach((control) => {
                        if (!control.name || !nextForm.elements[control.name]) return;
                        const value = nextForm.elements[control.name].value;
                        if (control._flatpickr) control._flatpickr.setDate(value, false);
                        else control.value = value;
                    });
                }

                function buildUrl() {
                    const url = new URL(form.action, window.location.href);
                    const params = new URLSearchParams(new FormData(form));
                    params.delete('page');
                    url.search = params.toString();
                    return url;
                }

                async function load(url, options = {}) {
                    clearTimeout(timer);
                    controller?.abort();
                    const current = new AbortController();
                    controller = current;
                    const mine = ++token;
                    errorBox.hidden = true;
                    setBusy(true);

                    try {
                        const response = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                            signal: current.signal,
                        });
                        if (response.status === 401 || response.status === 419) {
                            window.location.assign(url); // sesi berakhir: biarkan aplikasi mengarahkan ke login
                            return;
                        }
                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                        if (mine !== token) return;

                        const next = cfg.regions.map((selector) => doc.querySelector(selector));
                        if (next.some((el) => !el)) {
                            if (response.redirected) {
                                window.location.assign(url);
                                return;
                            }
                            throw new Error('Respons tidak lengkap');
                        }

                        cfg.regions.forEach((selector, index) => {
                            document.querySelector(selector).innerHTML = next[index].innerHTML;
                        });
                        (cfg.sync || []).forEach((selector) => syncElement(selector, doc));
                        if (options.syncForm) syncForm(doc);
                        window.history.replaceState(null, '', url);
                        cfg.onUpdate?.(doc, url);
                    } catch (error) {
                        if (error.name === 'AbortError' || mine !== token) return;
                        console.error(error);
                        errorBox.hidden = false;
                    } finally {
                        if (mine === token) setBusy(false);
                    }
                }

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    load(buildUrl());
                });

                search?.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => load(buildUrl()), 350);
                });

                document.addEventListener('click', (event) => {
                    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                    const reset = cfg.reset ? event.target.closest(cfg.reset) : null;
                    const link = reset || (cfg.links ? event.target.closest(cfg.links) : null);
                    if (!link || !link.href || link.target) return;

                    const url = new URL(link.href, window.location.href);
                    if (url.origin !== window.location.origin) return;
                    event.preventDefault();
                    load(url, { syncForm: !!reset });
                });

                return { load, refresh: () => load(new URL(window.location.href)) };
            },
        };
    })();
</script>
