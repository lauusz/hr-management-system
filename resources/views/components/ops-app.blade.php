@props(['title' => 'Kebutuhan Operasional'])
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
        :root { --ops-primary:#0F766E; --ops-dark:#115E59; --ops-soft:#E8F8F3; --ops-border:#BFE8DD; --ops-bg:#F6F8F8; --ops-text:#111827; --ops-muted:#6B7280; --ops-white:#fff; --ops-error:#DC2626; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--ops-bg); color:var(--ops-text); font-family:'Poppins',system-ui,sans-serif; }
        .ops-shell { min-height:100dvh; }
        .ops-sidebar { position:fixed; inset:0 auto 0 0; z-index:1000; width:min(86vw,304px); padding:16px 12px; background:var(--ops-white); border-right:1px solid var(--ops-border); transform:translateX(-100%); transition:.2s ease; display:flex; flex-direction:column; }
        .ops-sidebar.open { transform:translateX(0); }
        .ops-backdrop { display:none; position:fixed; inset:0; z-index:999; background:rgba(17,24,39,.38); }
        .ops-backdrop.show { display:block; }
        .ops-brand { padding:4px 8px 14px; border-bottom:1px solid #E5E7EB; }
        .ops-brand strong { display:block; font-size:15px; }
        .ops-brand span { color:var(--ops-muted); font-size:11px; }
        .ops-nav { flex:1; overflow:auto; padding-top:10px; }
        .ops-nav-title { margin:14px 10px 7px; color:#9CA3AF; font-size:10px; font-weight:800; text-transform:uppercase; }
        .ops-nav a { display:flex; align-items:center; gap:10px; min-height:44px; margin-bottom:4px; padding:10px 12px; border-radius:12px; color:var(--ops-muted); font-size:13px; font-weight:600; text-decoration:none; }
        .ops-nav a:hover,.ops-nav a.active { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-icon-sprite { position:absolute; width:0; height:0; overflow:hidden; }
        .ops-nav-icon { width:18px; height:18px; flex:0 0 18px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        .ops-user { padding:12px; border-radius:14px; background:var(--ops-soft); font-size:12px; }
        .ops-user strong { display:block; }
        .ops-user span { color:var(--ops-muted); font-size:11px; }
        .ops-main { min-height:100dvh; }
        .ops-topbar { position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:10px; min-height:62px; padding:9px 14px; background:rgba(246,248,248,.95); border-bottom:1px solid var(--ops-border); backdrop-filter:blur(10px); }
        .ops-burger,.ops-cart-link { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border:1px solid var(--ops-border); border-radius:13px; background:#fff; color:var(--ops-dark); text-decoration:none; cursor:pointer; }
        .ops-topbar-title { flex:1; min-width:0; }
        .ops-topbar-title strong { display:block; font-size:14px; }
        .ops-topbar-title span { display:block; color:var(--ops-muted); font-size:10px; }
        .ops-cart-link { position:relative; font-size:20px; }
        .ops-cart-count { position:absolute; top:-5px; right:-5px; min-width:20px; padding:2px 5px; border-radius:999px; background:var(--ops-error); color:#fff; font-size:10px; font-weight:800; text-align:center; }
        .ops-content { max-width:1200px; margin:auto; padding:16px 14px 80px; }
        .ops-alert { margin-bottom:12px; padding:12px 14px; border-radius:12px; font-size:13px; font-weight:600; }
        .ops-alert-success { color:#166534; background:#DCFCE7; }
        .ops-alert-warning { color:#92400E; background:#FEF3C7; }
        .ops-header { margin-bottom:16px; }
        .ops-title { margin:0; font-size:21px; font-weight:800; }
        .ops-subtitle { margin:4px 0 0; color:var(--ops-muted); font-size:12px; }
        .ops-grid { display:grid; grid-template-columns:1fr; gap:12px; }
        .ops-card { padding:16px; border:1px solid var(--ops-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-card h2,.ops-card h3 { margin:0 0 6px; font-size:15px; }
        .ops-muted { color:var(--ops-muted); font-size:12px; }
        .ops-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .ops-btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:0 15px; border:0; border-radius:12px; font:inherit; font-size:12px; font-weight:800; text-decoration:none; cursor:pointer; }
        .ops-btn-primary { color:#fff; background:var(--ops-primary); }
        .ops-btn-soft { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-btn-danger { color:#fff; background:var(--ops-error); }
        .ops-input,.ops-textarea,.ops-select { width:100%; min-height:44px; padding:10px 12px; border:1px solid var(--ops-border); border-radius:12px; background:#fff; font:inherit; font-size:13px; }
        .ops-textarea { min-height:90px; resize:vertical; }
        .ops-label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; }
        .ops-form-grid { display:grid; grid-template-columns:1fr; gap:12px; }
        .ops-badge { display:inline-flex; padding:5px 9px; border-radius:999px; background:var(--ops-soft); color:var(--ops-dark); font-size:10px; font-weight:800; }
        .ops-empty { padding:28px 16px; text-align:center; color:var(--ops-muted); }
        @media (min-width:640px) { .ops-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .ops-form-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (min-width:1024px) { .ops-shell { display:flex; } .ops-sidebar { position:sticky; top:12px; width:264px; height:calc(100dvh - 24px); margin:12px; border:1px solid var(--ops-border); border-radius:18px; transform:none; } .ops-backdrop,.ops-topbar { display:none!important; } .ops-main { flex:1; } .ops-content { padding:28px 32px 80px; } .ops-grid { grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); } }
    </style>
</head>
<body>
<div class="ops-shell">
    <div class="ops-backdrop" id="opsBackdrop"></div>
    <aside class="ops-sidebar" id="opsSidebar">
        <svg class="ops-icon-sprite" aria-hidden="true">
            <symbol id="ops-icon-catalog" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></symbol>
            <symbol id="ops-icon-cart" viewBox="0 0 24 24"><path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></symbol>
            <symbol id="ops-icon-request" viewBox="0 0 24 24"><path d="M9 5h6M9 3h6a2 2 0 0 1 2 2v1h2v15H5V6h2V5a2 2 0 0 1 2-2Z"/><path d="M9 11h6M9 15h6"/></symbol>
            <symbol id="ops-icon-inbox" viewBox="0 0 24 24"><path d="M4 4h16l2 10v6H2v-6L4 4Z"/><path d="M2 14h6l2 3h4l2-3h6"/></symbol>
            <symbol id="ops-icon-box" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></symbol>
            <symbol id="ops-icon-stock" viewBox="0 0 24 24"><path d="M7 3v15M3 7l4-4 4 4M17 21V6M13 17l4 4 4-4"/></symbol>
            <symbol id="ops-icon-access" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M16 11h6M19 8v6"/></symbol>
            <symbol id="ops-icon-portal" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
        </svg>
        <div class="ops-brand"><strong>Kebutuhan Operasional</strong><span>Kebutuhan operasional internal</span></div>
        <nav class="ops-nav">
            <div class="ops-nav-title">Menu</div>
            <a class="{{ request()->routeIs('v2.ops.catalog') ? 'active' : '' }}" href="{{ route('v2.ops.catalog') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-catalog"/></svg>Katalog</a>
            <a class="{{ request()->routeIs('v2.ops.cart.*') ? 'active' : '' }}" href="{{ route('v2.ops.cart.show') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-cart"/></svg>Keranjang</a>
            <a class="{{ request()->routeIs('v2.ops.requests.*') ? 'active' : '' }}" href="{{ route('v2.ops.requests.index') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-request"/></svg>Pengajuan Saya</a>
            @if(auth()->user()->canManageOps() && Route::has('v2.ops.admin.requests.index'))
                <div class="ops-nav-title">Admin OPS</div>
                <a class="{{ request()->routeIs('v2.ops.admin.requests.*') ? 'active' : '' }}" href="{{ route('v2.ops.admin.requests.index') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-inbox"/></svg>Request Masuk</a>
                @if(Route::has('v2.ops.admin.items.index'))<a class="{{ request()->routeIs('v2.ops.admin.items.*') ? 'active' : '' }}" href="{{ route('v2.ops.admin.items.index') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-box"/></svg>Master Barang OPS</a>@endif
                @if(Route::has('v2.ops.admin.stock-movements.index'))<a class="{{ request()->routeIs('v2.ops.admin.stock-movements.*') ? 'active' : '' }}" href="{{ route('v2.ops.admin.stock-movements.index') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-stock"/></svg>Riwayat Stok</a>@endif
                @if(Route::has('v2.ops.admin.access.index'))<a class="{{ request()->routeIs('v2.ops.admin.access.*') ? 'active' : '' }}" href="{{ route('v2.ops.admin.access.index') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-access"/></svg>Akses</a>@endif
            @endif
            <div class="ops-nav-title">Pindah</div>
            <a href="{{ route('v2.access') }}"><svg class="ops-nav-icon" aria-hidden="true"><use href="#ops-icon-portal"/></svg>Pilih Layanan</a>
        </nav>
        <div class="ops-user"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role->label() }}</span></div>
    </aside>
    <main class="ops-main">
        <header class="ops-topbar">
            <button class="ops-burger" id="opsBurger" type="button" aria-label="Buka menu">☰</button>
            <div class="ops-topbar-title"><strong>Kebutuhan Operasional</strong><span>Kebutuhan operasional internal</span></div>
            @php($opsCartCount = array_sum(session('ops_cart', [])))
            <a class="ops-cart-link" href="{{ route('v2.ops.cart.show') }}" aria-label="Keranjang">🛒@if($opsCartCount)<span class="ops-cart-count">{{ $opsCartCount > 99 ? '99+' : $opsCartCount }}</span>@endif</a>
        </header>
        <div class="ops-content">
            @if(session('success'))<div class="ops-alert ops-alert-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="ops-alert ops-alert-warning">{{ session('warning') }}</div>@endif
            @if($errors->any())<div class="ops-alert ops-alert-warning">{{ $errors->first() }}</div>@endif
            {{ $slot }}
        </div>
    </main>
</div>
<script>
    (() => { const side=document.getElementById('opsSidebar'), btn=document.getElementById('opsBurger'), back=document.getElementById('opsBackdrop'); if(!side||!btn||!back)return; const open=value=>{side.classList.toggle('open',value);back.classList.toggle('show',value);document.body.style.overflow=value?'hidden':''}; btn.addEventListener('click',()=>open(!side.classList.contains('open'))); back.addEventListener('click',()=>open(false)); side.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>open(false))); })();
</script>
</body>
</html>
