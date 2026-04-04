<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <title>HRD Triguna Samudratrans</title>

  <link rel="icon" href="{{ asset('images/logo-triguna-clean.png') }}" type="image/png">

  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1e40af;
      --primary-light: #dbeafe;
      --primary-xlight: #eff6ff;
      --success: #059669;
      --danger: #dc2626;
      --danger-bg: #fef2f2;
      --gray-50: #f8fafc;
      --gray-100: #f1f5f9;
      --gray-200: #e2e8f0;
      --gray-300: #cbd5e1;
      --gray-400: #94a3b8;
      --gray-500: #64748b;
      --gray-600: #475569;
      --gray-700: #334155;
      --gray-900: #0f172a;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--gray-50);
      color: var(--gray-900);
      min-height: 100vh;
    }

    /* Split Layout */
    .login-root {
      display: flex;
      min-height: 100vh;
    }

    /* Left Panel — Branding */
    .brand-panel {
      display: none;
      flex: 1;
      background: linear-gradient(145deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
      padding: 48px 40px;
      position: relative;
      overflow: hidden;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .brand-panel::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 320px;
      height: 320px;
      background: rgba(255,255,255,0.06);
      border-radius: 50%;
    }

    .brand-panel::after {
      content: '';
      position: absolute;
      bottom: -120px;
      left: -60px;
      width: 400px;
      height: 400px;
      background: rgba(255,255,255,0.04);
      border-radius: 50%;
    }

    .brand-content {
      position: relative;
      z-index: 1;
      text-align: center;
      max-width: 380px;
    }

    .brand-logo {
      width: 80px;
      height: 80px;
      background: rgba(255,255,255,0.15);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
    }

    .brand-logo img {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }

    .brand-name {
      font-size: 26px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 0.3px;
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .brand-tagline {
      font-size: 14px;
      color: rgba(255,255,255,0.7);
      line-height: 1.6;
      font-weight: 400;
    }

    .brand-divider {
      width: 40px;
      height: 3px;
      background: rgba(255,255,255,0.4);
      border-radius: 999px;
      margin: 20px auto;
    }

    /* Right Panel — Form */
    .form-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 20px;
      background: var(--gray-50);
    }

    .form-card {
      width: 100%;
      max-width: 400px;
      animation: fadeUp 0.3s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .form-header {
      margin-bottom: 28px;
    }

    .form-logo-mobile {
      width: 75%;
      height: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
      aspect-ratio: 1 / 1;
    }

    .form-logo-mobile img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .form-title {
      font-size: 22px;
      font-weight: 800;
      color: var(--gray-900);
      margin-bottom: 4px;
      letter-spacing: -0.3px;
    }

    .form-subtitle {
      font-size: 13.5px;
      color: var(--gray-500);
    }

    /* Error */
    .error-box {
      background: var(--danger-bg);
      border: 1px solid #fecaca;
      color: #991b1b;
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 13.5px;
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
      line-height: 1.5;
    }

    .error-box svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

    /* Form Fields */
    .field { margin-bottom: 16px; }

    .field-label {
      display: block;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--gray-600);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .field-input {
      width: 100%;
      height: 48px;
      padding: 0 14px;
      font-size: 15px;
      border: 1.5px solid var(--gray-200);
      border-radius: 12px;
      background: #fff;
      color: var(--gray-900);
      font-family: inherit;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    .field-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }

    .field-input::placeholder { color: var(--gray-400); }

    /* Remember */
    .remember-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 16px 0 22px;
    }

    .remember-row input[type="checkbox"] {
      width: 17px;
      height: 17px;
      accent-color: var(--primary);
      cursor: pointer;
      flex-shrink: 0;
    }

    .remember-row label {
      font-size: 13.5px;
      color: var(--gray-600);
      cursor: pointer;
      user-select: none;
    }

    /* Submit Button */
    .btn-submit {
      width: 100%;
      height: 50px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      letter-spacing: 0.2px;
    }

    .btn-submit:hover {
      background: var(--primary-dark);
      box-shadow: 0 4px 14px rgba(37,99,235,0.3);
    }

    .btn-submit:active { transform: scale(0.99); }

    .btn-submit svg { width: 18px; height: 18px; }

    /* Footer */
    .form-footer {
      margin-top: 24px;
      text-align: center;
      font-size: 12px;
      color: var(--gray-400);
    }

    /* Desktop — show brand panel */
    @media (min-width: 900px) {
      .login-root { flex-direction: row; }
      .brand-panel { display: flex; }
      .form-logo-mobile { display: none; }
    }

    /* Tablet */
    @media (min-width: 600px) and (max-width: 899px) {
      .form-panel { padding: 48px 32px; }
      .form-card { max-width: 440px; }
    }

    /* Mobile adjustments */
    @media (max-width: 599px) {
      .brand-panel { display: none; }
      .form-logo-mobile { display: flex; }
      .form-title { font-size: 20px; }
    }
  </style>
</head>

<body>

<div class="login-root">

  {{-- Left: Brand Panel (desktop only) --}}
  <div class="brand-panel">
    <div class="brand-content">
      <div class="brand-logo">
        <img src="{{ asset('images/logo-triguna-clean.png') }}" alt="Triguna Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:28px; font-weight:800; color:#fff;\'>TG</span>';">
      </div>
      <div class="brand-name">HRD Triguna<br>Samudratrans</div>
      <div class="brand-divider"></div>
      <div class="brand-tagline">Sistem Manajemen Kehadiran & Cuti Berbasis Digital</div>
    </div>
  </div>

  {{-- Right: Form Panel --}}
  <div class="form-panel">
    <div class="form-card">

      {{-- Mobile Logo --}}
      <div class="form-logo-mobile">
        <img src="{{ asset('images/triguna-logo.png') }}" alt="Triguna Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:28px; font-weight:800; color:var(--primary);\'>TG</span>';">
      </div>

      <div class="form-header">
        <h1 class="form-title">Masuk</h1>
        <p class="form-subtitle">Gunakan Email yang terdaftar di HRD Triguna Anda</p>
      </div>

      @if ($errors->any())
      <div class="error-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ $errors->first() }}</span>
      </div>
      @endif

      <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
          <label class="field-label" for="username">Email</label>
          <input
            id="username"
            name="username"
            type="text"
            class="field-input"
            value="{{ old('username') }}"
            inputmode="email"
            autocomplete="username"
            autofocus
            placeholder="nama@triguna.co.id">
        </div>

        <div class="field">
          <label class="field-label" for="password">Password</label>
          <input
            id="password"
            name="password"
            type="password"
            class="field-input"
            autocomplete="current-password"
            required
            placeholder="••••••••">
        </div>

        <div class="remember-row">
          <input id="remember" type="checkbox" name="remember">
          <label for="remember">Ingat saya</label>
        </div>

        <button class="btn-submit" type="submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Masuk
        </button>
      </form>

      <div class="form-footer">
        &copy; {{ date('Y') }} HRD Triguna Samudratrans
      </div>

    </div>
  </div>

</div>

</body>
</html>
