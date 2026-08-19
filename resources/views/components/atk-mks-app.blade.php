@props(['title' => 'Stok ATK MKS'])
@php
    $atkMksCartCount = array_sum(session('atk_mks_cart', []));
    $atkMksPendingRequestCount = auth()->check() && auth()->user()->canManageAtkMks()
        ? \App\Models\AtkRequest::query()
            ->forModule(\App\Models\AtkRequest::MODULE_ATK_MKS)
            ->where('status', \App\Models\AtkRequest::STATUS_PENDING)
            ->count()
        : 0;
    $atkMksPendingNeedRequestCount = auth()->check() && auth()->user()->canManageAtkMks()
        ? \App\Models\AtkNeedRequest::query()
            ->forModule(\App\Models\AtkNeedRequest::MODULE_ATK_MKS)
            ->where('status', \App\Models\AtkNeedRequest::STATUS_PENDING)
            ->count()
        : 0;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --atk-mks-primary:#A16207; --atk-mks-dark:#78350F; --atk-mks-soft:#FEF3C7; --atk-mks-border:#F5D77C; --atk-mks-bg:#FFFCF4; --atk-mks-text:#111827; --atk-mks-muted:#6B7280; --atk-mks-white:#fff; --atk-mks-error:#DC2626; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--atk-mks-bg); color:var(--atk-mks-text); font-family:'Poppins',system-ui,sans-serif; }
        .atk-mks-shell { min-height:100dvh; }
        .atk-mks-sidebar { position:fixed; inset:0 auto 0 0; z-index:1000; width:min(86vw,304px); padding:16px 12px; background:var(--atk-mks-white); border-right:1px solid var(--atk-mks-border); transform:translateX(-100%); transition:.2s ease; display:flex; flex-direction:column; }
        .atk-mks-sidebar.open { transform:translateX(0); }
        .atk-mks-backdrop { display:none; position:fixed; inset:0; z-index:999; background:rgba(17,24,39,.38); }
        .atk-mks-backdrop.show { display:block; }
        .atk-mks-brand { padding:4px 8px 14px; border-bottom:1px solid #E5E7EB; }
        .atk-mks-brand strong { display:block; font-size:15px; }
        .atk-mks-brand span { color:var(--atk-mks-muted); font-size:11px; }
        .atk-mks-nav { flex:1; overflow:auto; padding-top:10px; }
        .atk-mks-nav-title { margin:14px 10px 7px; color:#9CA3AF; font-size:10px; font-weight:800; text-transform:uppercase; }
        .atk-mks-nav a { display:flex; align-items:center; gap:10px; min-height:44px; margin-bottom:4px; padding:10px 12px; border-radius:12px; color:var(--atk-mks-muted); font-size:13px; font-weight:600; text-decoration:none; }
        .atk-mks-nav a:hover,.atk-mks-nav a.active { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-nav-badge { min-width:18px; margin-left:auto; padding:2px 7px; border-radius:999px; background:var(--atk-mks-error); color:#fff; font-size:10px; font-weight:700; line-height:1.4; text-align:center; }
        .atk-mks-icon-sprite { position:absolute; width:0; height:0; overflow:hidden; }
        .atk-mks-nav-icon { width:18px; height:18px; flex:0 0 18px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        .atk-mks-user { padding:12px; border-radius:14px; background:var(--atk-mks-soft); font-size:12px; }
        .atk-mks-user strong { display:block; }
        .atk-mks-user span { color:var(--atk-mks-muted); font-size:11px; }
        .atk-mks-sidebar-footer { padding-top:12px; border-top:1px solid #E5E7EB; }
        .atk-mks-logout { width:100%; margin-top:8px; color:var(--atk-mks-muted); background:#F1F1F3; }
        .atk-mks-main { min-height:100dvh; }
        .atk-mks-topbar { position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:10px; min-height:62px; padding:9px 14px; background:rgba(246,248,248,.95); border-bottom:1px solid var(--atk-mks-border); backdrop-filter:blur(10px); }
        .atk-mks-burger,.atk-mks-cart-link { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border:1px solid var(--atk-mks-border); border-radius:13px; background:#fff; color:var(--atk-mks-dark); text-decoration:none; cursor:pointer; }
        .atk-mks-topbar-title { flex:1; min-width:0; }
        .atk-mks-topbar-title strong { display:block; font-size:14px; }
        .atk-mks-topbar-title span { display:block; color:var(--atk-mks-muted); font-size:10px; }
        .atk-mks-cart-link { position:relative; font-size:20px; }
        .atk-mks-cart-count { position:absolute; top:-5px; right:-5px; min-width:20px; padding:2px 5px; border-radius:999px; background:var(--atk-mks-error); color:#fff; font-size:10px; font-weight:800; text-align:center; }
        .atk-mks-content { max-width:1200px; margin:auto; padding:16px 14px 80px; }
        .atk-mks-alert { margin-bottom:12px; padding:12px 14px; border-radius:12px; font-size:13px; font-weight:600; }
        .atk-mks-alert-success { color:#166534; background:#DCFCE7; }
        .atk-mks-alert-warning { color:#92400E; background:#FEF3C7; }
        .atk-mks-header { margin-bottom:16px; }
        .atk-mks-title { margin:0; font-size:21px; font-weight:800; }
        .atk-mks-subtitle { margin:4px 0 0; color:var(--atk-mks-muted); font-size:12px; }
        .atk-mks-grid { display:grid; grid-template-columns:1fr; gap:12px; }
        .atk-mks-card { padding:16px; border:1px solid var(--atk-mks-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-card h2,.atk-mks-card h3 { margin:0 0 6px; font-size:15px; }
        .atk-mks-muted { color:var(--atk-mks-muted); font-size:12px; }
        .atk-mks-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .atk-mks-btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:0 15px; border:0; border-radius:12px; font:inherit; font-size:12px; font-weight:800; text-decoration:none; cursor:pointer; }
        .atk-mks-btn-primary { color:#fff; background:var(--atk-mks-primary); }
        .atk-mks-btn-soft { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-btn-danger { color:#fff; background:var(--atk-mks-error); }
        .atk-mks-input,.atk-mks-textarea,.atk-mks-select { width:100%; min-height:44px; padding:10px 12px; border:1px solid var(--atk-mks-border); border-radius:12px; background:#fff; font:inherit; font-size:13px; }
        .atk-mks-textarea { min-height:90px; resize:vertical; }
        .atk-mks-label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; }
        .atk-mks-form-grid { display:grid; grid-template-columns:1fr; gap:12px; }
        .atk-mks-badge { display:inline-flex; padding:5px 9px; border-radius:999px; background:var(--atk-mks-soft); color:var(--atk-mks-dark); font-size:10px; font-weight:800; }
        .atk-mks-empty { padding:28px 16px; text-align:center; color:var(--atk-mks-muted); }
        @media (min-width:640px) { .atk-mks-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .atk-mks-form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (min-width:1024px) { .atk-mks-shell { display:flex; } .atk-mks-sidebar { position:sticky; top:12px; width:264px; height:calc(100dvh - 24px); margin:12px; border:1px solid var(--atk-mks-border); border-radius:18px; transform:none; } .atk-mks-backdrop,.atk-mks-topbar { display:none!important; } .atk-mks-main { flex:1; } .atk-mks-content { padding:28px 32px 80px; } .atk-mks-grid { grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); } }
    </style>
</head>
<body>
<div class="atk-mks-shell">
    <div class="atk-mks-backdrop" id="atkMksBackdrop"></div>
    <aside class="atk-mks-sidebar" id="atkMksSidebar">
        <svg class="atk-mks-icon-sprite" aria-hidden="true">
            <symbol id="atk-mks-icon-catalog" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></symbol>
            <symbol id="atk-mks-icon-cart" viewBox="0 0 24 24"><path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></symbol>
            <symbol id="atk-mks-icon-request" viewBox="0 0 24 24"><path d="M9 5h6M9 3h6a2 2 0 0 1 2 2v1h2v15H5V6h2V5a2 2 0 0 1 2-2Z"/><path d="M9 11h6M9 15h6"/></symbol>
            <symbol id="atk-mks-icon-inbox" viewBox="0 0 24 24"><path d="M4 4h16l2 10v6H2v-6L4 4Z"/><path d="M2 14h6l2 3h4l2-3h6"/></symbol>
            <symbol id="atk-mks-icon-box" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></symbol>
            <symbol id="atk-mks-icon-stock" viewBox="0 0 24 24"><path d="M7 3v15M3 7l4-4 4 4M17 21V6M13 17l4 4 4-4"/></symbol>
            <symbol id="atk-mks-icon-access" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M16 11h6M19 8v6"/></symbol>
            <symbol id="atk-mks-icon-portal" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
        </svg>
        <div class="atk-mks-brand"><strong>Stok ATK MKS</strong><span>Persediaan ATK khusus MKS</span></div>
        <nav class="atk-mks-nav">
            <div class="atk-mks-nav-title">Menu</div>
            <a class="{{ request()->routeIs('v2.atk-mks.catalog') ? 'active' : '' }}" href="{{ route('v2.atk-mks.catalog') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-catalog"/></svg>Katalog</a>
            <a class="{{ request()->routeIs('v2.atk-mks.cart.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.cart.show') }}">
                <svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-cart"/></svg>
                <span>Keranjang</span>
                @if($atkMksCartCount > 0)
                    <span class="atk-mks-nav-badge atk-mks-cart-nav-badge">{{ $atkMksCartCount > 99 ? '99+' : $atkMksCartCount }}</span>
                @endif
            </a>
            <a class="{{ request()->routeIs('v2.atk-mks.requests.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.requests.index') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-request"/></svg>Pengajuan Saya</a>
            @if(auth()->user()->canManageAtkMks() && Route::has('v2.atk-mks.admin.requests.index'))
                <div class="atk-mks-nav-title">Admin ATK MKS</div>
                <a class="{{ request()->routeIs('v2.atk-mks.admin.requests.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.admin.requests.index') }}">
                    <svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-inbox"/></svg>
                    <span>Request Masuk</span>
                    @if($atkMksPendingRequestCount > 0)
                        <span class="atk-mks-nav-badge">{{ $atkMksPendingRequestCount > 99 ? '99+' : $atkMksPendingRequestCount }}</span>
                    @endif
                </a>
                @if(Route::has('v2.atk-mks.admin.need-requests.index'))
                    <a class="{{ request()->routeIs('v2.atk-mks.admin.need-requests.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.admin.need-requests.index') }}">
                        <svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-request"/></svg>
                        <span>Request Barang</span>
                        @if($atkMksPendingNeedRequestCount > 0)
                            <span class="atk-mks-nav-badge atk-mks-need-request-nav-badge">{{ $atkMksPendingNeedRequestCount > 99 ? '99+' : $atkMksPendingNeedRequestCount }}</span>
                        @endif
                    </a>
                @endif
                @if(Route::has('v2.atk-mks.admin.items.index'))<a class="{{ request()->routeIs('v2.atk-mks.admin.items.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.admin.items.index') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-box"/></svg>Master Barang ATK MKS</a>@endif
                @if(Route::has('v2.atk-mks.admin.stock-movements.index'))<a class="{{ request()->routeIs('v2.atk-mks.admin.stock-movements.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.admin.stock-movements.index') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-stock"/></svg>Riwayat Stok</a>@endif
                @if(Route::has('v2.atk-mks.admin.access.index'))<a class="{{ request()->routeIs('v2.atk-mks.admin.access.*') ? 'active' : '' }}" href="{{ route('v2.atk-mks.admin.access.index') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-access"/></svg>Akses</a>@endif
            @endif
            <div class="atk-mks-nav-title">Pindah</div>
            <a href="{{ route('v2.access') }}"><svg class="atk-mks-nav-icon" aria-hidden="true"><use href="#atk-mks-icon-portal"/></svg>Pilih Layanan</a>
        </nav>
        <div class="atk-mks-sidebar-footer">
            <div class="atk-mks-user"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role->label() }}</span></div>
            <button class="atk-mks-btn atk-mks-logout" type="button" data-modal-target="confirm-logout" aria-haspopup="dialog" aria-controls="confirm-logout">Keluar</button>
        </div>
    </aside>
    <main class="atk-mks-main">
        <header class="atk-mks-topbar">
            <button class="atk-mks-burger" id="atkMksBurger" type="button" aria-label="Buka menu">☰</button>
            <div class="atk-mks-topbar-title"><strong>Stok ATK MKS</strong><span>Persediaan ATK khusus MKS</span></div>
            <a class="atk-mks-cart-link" href="{{ route('v2.atk-mks.cart.show') }}" aria-label="Keranjang">🛒@if($atkMksCartCount)<span class="atk-mks-cart-count">{{ $atkMksCartCount > 99 ? '99+' : $atkMksCartCount }}</span>@endif</a>
        </header>
        <div class="atk-mks-content">
            @if(session('success'))<div class="atk-mks-alert atk-mks-alert-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="atk-mks-alert atk-mks-alert-warning">{{ session('warning') }}</div>@endif
            @if($errors->any())<div class="atk-mks-alert atk-mks-alert-warning">{{ $errors->first() }}</div>@endif
            {{ $slot }}
        </div>
    </main>
</div>
<x-modal
    id="confirm-logout"
    title="Keluar dari Sistem?"
    type="confirm"
    variant="danger"
    confirmLabel="Ya, Keluar"
    cancelLabel="Batal"
    :confirmFormAction="route('logout')"
    confirmFormMethod="POST">
    <p style="margin:0;">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
</x-modal>
<script>
    (() => {
        const side=document.getElementById('atkMksSidebar'), btn=document.getElementById('atkMksBurger'), back=document.getElementById('atkMksBackdrop');
        const logoutModal=document.getElementById('confirm-logout'), logoutTrigger=document.querySelector('[data-modal-target="confirm-logout"]');
        if(!side||!btn||!back)return;
        const open=value=>{side.classList.toggle('open',value);back.classList.toggle('show',value);document.body.style.overflow=value?'hidden':''};
        const openLogout=value=>{if(!logoutModal)return;logoutModal.style.display=value?'flex':'none';document.body.style.overflow=value?'hidden':''};
        btn.addEventListener('click',()=>open(!side.classList.contains('open')));
        back.addEventListener('click',()=>open(false));
        side.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>open(false)));
        logoutTrigger?.addEventListener('click',()=>{open(false);openLogout(true)});
        logoutModal?.addEventListener('click',event=>{if(event.target===logoutModal||event.target.closest('[data-modal-close]'))openLogout(false)});
        document.addEventListener('keydown',event=>{if(event.key==='Escape'){open(false);openLogout(false)}});
    })();
</script>
</body>
</html>

