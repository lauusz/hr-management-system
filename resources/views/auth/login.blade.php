<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

  {{-- PWA Meta Tags --}}
  <meta name="theme-color" content="#0A3D62">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="HRD System">

  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

  <title>Masuk — HRD Triguna Samudratrans</title>
  <link rel="icon" href="{{ asset('images/logo-triguna-clean.png') }}" type="image/png">

  {{-- Plus Jakarta Sans --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #0A3D62;
      --primary: #145DA0;
      --primary-light: #1E81B0;
      --accent: #D4AF37;
      --accent-light: #E6C65C;
      --accent-dark: #B8962E;

      --white: #FFFFFF;
      --gray-50: #F5F7FA;
      --gray-100: #F8FAFC;
      --border: #E5E7EB;

      --text-primary: #111827;
      --text-secondary: #374151;
      --text-muted: #6B7280;

      --success: #22C55E;
      --warning: #F59E0B;
      --error: #EF4444;
      --info: #3B82F6;

      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
      --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);

      --radius-md: 8px;
      --radius-lg: 12px;
      --radius-xl: 16px;
      --radius-2xl: 20px;
      --radius-full: 9999px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      -webkit-text-size-adjust: 100%;
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
      background: var(--primary-dark);
      color: var(--text-primary);
      min-height: 100vh;
      min-height: 100dvh;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* ========================================
       BASE (320px – 359px)
       Ultra-compact for small phones
       ======================================== */

    .login-root {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      min-height: 100dvh;
      padding: 16px 12px;
      padding-top: max(16px, env(safe-area-inset-top));
      padding-bottom: max(16px, env(safe-area-inset-bottom));
      padding-left: max(12px, env(safe-area-inset-left));
      padding-right: max(12px, env(safe-area-inset-right));
      position: relative;
      overflow: hidden;
    }

    /* Subtle background shapes — organic, not perfect circles */
    .bg-shape {
      position: absolute;
      pointer-events: none;
      opacity: 0.04;
    }

    .bg-shape-1 {
      width: 260px;
      height: 220px;
      background: radial-gradient(ellipse at 30% 40%, rgba(255,255,255,0.9) 0%, transparent 70%);
      top: -50px;
      right: -100px;
      border-radius: 60% 40% 55% 45%;
    }

    .bg-shape-2 {
      width: 220px;
      height: 260px;
      background: radial-gradient(ellipse at 60% 30%, rgba(255,255,255,0.7) 0%, transparent 70%);
      bottom: 30px;
      left: -80px;
      border-radius: 45% 55% 40% 60%;
    }

    .bg-shape-3 {
      width: 120px;
      height: 120px;
      background: radial-gradient(circle, rgba(212,175,55,0.25) 0%, transparent 70%);
      top: 38%;
      right: 5%;
      border-radius: 55% 45% 50% 50%;
    }

    /* Subtle route-like lines suggesting logistics/maritime */
    .route-line {
      position: absolute;
      pointer-events: none;
      stroke: rgba(255,255,255,0.04);
      stroke-width: 1.5;
      fill: none;
      stroke-linecap: round;
    }

    /* Brand section above card */
    .brand-section {
      text-align: center;
      margin-bottom: 14px;
      position: relative;
      z-index: 1;
    }

    .brand-logo {
      width: 56px;
      height: auto;
      margin-bottom: 6px;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.12));
    }

    .brand-name {
      color: var(--white);
      font-size: 0.9375rem;
      font-weight: 800;
      line-height: 1.3;
      letter-spacing: -0.01em;
    }

    .brand-tagline {
      color: rgba(255, 255, 255, 0.55);
      font-size: 0.6875rem;
      margin-top: 1px;
      font-weight: 500;
    }

    /* Login card — white, warm, grounded */
    .form-card {
      width: 100%;
      max-width: 340px;
      background: var(--white);
      border-radius: 12px;
      padding: 18px 16px;
      box-shadow:
        0 1px 2px rgba(0,0,0,0.03),
        0 4px 12px rgba(0,0,0,0.05),
        0 12px 24px rgba(0,0,0,0.06);
      position: relative;
      z-index: 1;
    }

    /* Gold accent line — sparing, purposeful */
    .card-accent {
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 48px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--accent-light));
      border-radius: 0 0 4px 4px;
    }

    .form-header {
      margin-bottom: 12px;
    }

    .form-title {
      font-size: 1.0625rem;
      font-weight: 800;
      color: var(--text-primary);
      letter-spacing: -0.02em;
      line-height: 1.25;
    }

    .form-subtitle {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 2px;
      line-height: 1.5;
    }

    /* Error box — reserved space, does not shift layout */
    .error-box {
      background: rgba(239, 68, 68, 0.06);
      border: 1px solid rgba(239, 68, 68, 0.15);
      padding: 6px 10px;
      border-radius: var(--radius-lg);
      margin-bottom: 8px;
      color: var(--error);
      font-size: 0.75rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      min-height: 34px;
    }

    .error-box:empty {
      display: none;
    }

    .error-icon {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      color: var(--error);
    }

    /* Fields */
    .field {
      margin-bottom: 10px;
    }

    .field-label {
      display: block;
      font-size: 0.625rem;
      font-weight: 700;
      color: var(--text-secondary);
      margin-bottom: 3px;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .input-wrap {
      position: relative;
    }

    .field-input {
      width: 100%;
      height: 44px;
      padding: 0 12px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      background: var(--white);
      font-family: inherit;
      font-size: 0.9375rem;
      color: var(--text-primary);
      transition: all 0.2s ease;
      -webkit-appearance: none;
      appearance: none;
    }

    .field-input::placeholder {
      color: var(--text-muted);
      opacity: 1;
    }

    .field-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1), 0 0 0 1px rgba(212, 175, 55, 0.15);
      outline: none;
    }

    .field-input:hover:not(:focus) {
      border-color: #D1D5DB;
    }

    .field-input.input-error {
      border-color: var(--error);
    }

    .field-input.input-error:focus {
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    /* Password toggle */
    .password-toggle {
      position: absolute;
      right: 2px;
      top: 50%;
      transform: translateY(-50%);
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
      border-radius: var(--radius-md);
      transition: all 0.15s ease;
    }

    .password-toggle:hover {
      color: var(--text-secondary);
      background: var(--gray-50);
    }

    .password-toggle:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: -2px;
    }

    .password-toggle svg {
      width: 18px;
      height: 18px;
    }

    /* Remember me */
    .remember-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 0 0 14px;
    }

    .custom-checkbox {
      position: relative;
      display: flex;
      align-items: center;
      cursor: pointer;
      user-select: none;
      min-height: 44px;
    }

    .custom-checkbox input {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }

    .checkbox-mark {
      width: 22px;
      height: 22px;
      min-width: 22px;
      border: 2px solid var(--border);
      border-radius: 6px;
      background: var(--white);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .checkbox-mark svg {
      width: 13px;
      height: 13px;
      opacity: 0;
      transform: scale(0.5);
      transition: all 0.15s ease;
    }

    .checkbox-label {
      font-size: 0.8125rem;
      color: var(--text-secondary);
      font-weight: 500;
      margin-left: 10px;
    }

    .custom-checkbox input:checked ~ .checkbox-mark {
      background: var(--primary);
      border-color: var(--primary);
      box-shadow: inset 0 0 0 1px rgba(212, 175, 55, 0.4);
    }

    .custom-checkbox input:checked ~ .checkbox-mark svg {
      opacity: 1;
      transform: scale(1);
    }

    .custom-checkbox input:focus-visible ~ .checkbox-mark {
      box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.25);
    }

    .custom-checkbox input:checked:focus-visible ~ .checkbox-mark {
      box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.25), inset 0 0 0 1px rgba(212, 175, 55, 0.4);
    }

    /* Submit button */
    .btn-submit {
      width: 100%;
      height: 44px;
      border-radius: 10px;
      border: none;
      color: var(--white);
      font-family: inherit;
      font-weight: 700;
      font-size: 0.875rem;
      cursor: pointer;
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      box-shadow: 0 3px 10px rgba(10, 61, 98, 0.22);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      position: relative;
    }

    .btn-submit:hover:not(:disabled) {
      background: linear-gradient(135deg, #082D4A, var(--primary-dark));
      box-shadow: 0 6px 20px rgba(10, 61, 98, 0.38);
      transform: translateY(-1px);
    }

    .btn-submit:active:not(:disabled) {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(10, 61, 98, 0.28);
    }

    .btn-submit:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    /* Loading spinner */
    .spinner {
      width: 16px;
      height: 16px;
      border: 2.5px solid rgba(255,255,255,0.3);
      border-top-color: var(--white);
      border-radius: 50%;
      animation: spin 0.7s linear infinite;
      flex-shrink: 0;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .btn-text {
      transition: opacity 0.2s ease;
    }

    .btn-submit.is-loading .btn-text {
      opacity: 0.85;
    }

    /* Footer */
    .form-footer {
      margin-top: 12px;
      font-size: 0.625rem;
      color: var(--text-muted);
      text-align: center;
      line-height: 1.5;
    }

    .form-footer strong {
      color: var(--text-secondary);
      font-weight: 600;
    }

    /* ========================================
       SMALL-MEDIUM PHONES (360px – 479px)
       Comfortable compact for most devices
       ======================================== */
    @media (min-width: 360px) {
      .login-root {
        padding: 20px 14px;
        padding-top: max(20px, env(safe-area-inset-top));
        padding-bottom: max(20px, env(safe-area-inset-bottom));
        padding-left: max(14px, env(safe-area-inset-left));
        padding-right: max(14px, env(safe-area-inset-right));
      }

      .bg-shape-1 {
        width: 320px;
        height: 280px;
        top: -60px;
        right: -120px;
      }

      .bg-shape-2 {
        width: 260px;
        height: 320px;
        bottom: 40px;
        left: -100px;
      }

      .bg-shape-3 {
        width: 140px;
        height: 140px;
      }

      .brand-section {
        margin-bottom: 18px;
      }

      .brand-logo {
        width: 64px;
        margin-bottom: 8px;
      }

      .brand-name {
        font-size: 1rem;
      }

      .brand-tagline {
        font-size: 0.75rem;
        margin-top: 2px;
      }

      .form-card {
        max-width: 360px;
        border-radius: 14px;
        padding: 20px 18px;
      }


      .form-header {
        margin-bottom: 14px;
      }

      .form-title {
        font-size: 1.125rem;
      }

      .error-box {
        padding: 8px 12px;
        margin-bottom: 10px;
        min-height: 36px;
      }

      .field {
        margin-bottom: 12px;
      }

      .field-label {
        margin-bottom: 4px;
      }

      .field-input {
        height: 46px;
        padding: 0 14px;
      }

      .password-toggle {
        right: 4px;
        width: 44px;
        height: 44px;
      }

      .password-toggle svg {
        width: 20px;
        height: 20px;
      }

      .remember-row {
        margin: 2px 0 18px;
      }

      .btn-submit {
        height: 46px;
      }

      .spinner {
        width: 18px;
        height: 18px;
      }

      .form-footer {
        margin-top: 14px;
      }
    }

    /* ========================================
       LARGE PHONES (480px – 639px)
       More breathing room for big screens
       ======================================== */
    @media (min-width: 480px) {
      .login-root {
        padding: 24px 18px;
        padding-top: max(24px, env(safe-area-inset-top));
        padding-bottom: max(24px, env(safe-area-inset-bottom));
        padding-left: max(18px, env(safe-area-inset-left));
        padding-right: max(18px, env(safe-area-inset-right));
      }

      .brand-section {
        margin-bottom: 22px;
      }

      .brand-logo {
        width: 72px;
        margin-bottom: 10px;
      }

      .brand-name {
        font-size: 1.0625rem;
      }

      .brand-tagline {
        font-size: 0.8125rem;
        margin-top: 3px;
      }

      .form-card {
        max-width: 400px;
        border-radius: 16px;
        padding: 26px 24px;
        box-shadow:
          0 1px 2px rgba(0,0,0,0.03),
          0 6px 16px rgba(0,0,0,0.06),
          0 16px 32px rgba(0,0,0,0.08);
      }


      .form-header {
        margin-bottom: 18px;
      }

      .form-title {
        font-size: 1.25rem;
      }

      .form-subtitle {
        font-size: 0.8125rem;
      }

      .error-box {
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 0.8125rem;
        min-height: 40px;
      }

      .field {
        margin-bottom: 14px;
      }

      .field-label {
        font-size: 0.6875rem;
        margin-bottom: 6px;
      }

      .field-input {
        height: 50px;
        padding: 0 16px;
        border-radius: 12px;
      }

      .remember-row {
        margin: 4px 0 22px;
      }

      .checkbox-label {
        font-size: 0.875rem;
      }

      .btn-submit {
        height: 50px;
        border-radius: 12px;
        font-size: 0.9375rem;
      }

      .form-footer {
        margin-top: 18px;
        font-size: 0.6875rem;
      }
    }

    /* ========================================
       TABLET ENHANCEMENT (640px+)
       ======================================== */
    @media (min-width: 640px) {
      .login-root {
        padding: 28px 24px;
      }

      .form-card {
        padding: 28px 28px;
        border-radius: var(--radius-2xl);
        max-width: 420px;
      }

      .brand-logo {
        width: 76px;
      }

      .brand-section {
        margin-bottom: 24px;
      }
    }

    /* ========================================
       DESKTOP ENHANCEMENT (1024px+)
       ======================================== */
    @media (min-width: 1024px) {
      body {
        background:
          radial-gradient(ellipse 80% 60% at 15% 20%, rgba(20, 93, 160, 0.18) 0%, transparent 60%),
          radial-gradient(ellipse 60% 50% at 85% 80%, rgba(30, 129, 176, 0.12) 0%, transparent 55%),
          linear-gradient(165deg, #072a45 0%, var(--primary-dark) 40%, #083054 70%, #061f35 100%);
      }

      .login-root {
        padding: 40px 32px;
      }

      /* More pronounced but still subtle organic shapes */
      .bg-shape-1 {
        width: 520px;
        height: 420px;
        top: -140px;
        right: -200px;
        opacity: 0.05;
      }

      .bg-shape-2 {
        width: 400px;
        height: 480px;
        bottom: -100px;
        left: -160px;
        opacity: 0.045;
      }

      .bg-shape-3 {
        width: 180px;
        height: 180px;
        top: 30%;
        right: 10%;
        opacity: 0.07;
      }

      /* Subtle abstract route lines — logistics inspired */
      .route-line-desktop {
        position: absolute;
        pointer-events: none;
        z-index: 0;
      }

      .route-line-desktop path {
        stroke: rgba(255,255,255,0.035);
        stroke-width: 1.2;
        fill: none;
        stroke-linecap: round;
        stroke-dasharray: 6 10;
      }

      .brand-section {
        margin-bottom: 20px;
      }

      .brand-logo {
        width: 72px;
        margin-bottom: 10px;
      }

      .brand-name {
        font-size: 1.0625rem;
      }

      .brand-tagline {
        font-size: 0.75rem;
      }

      .form-card {
        max-width: 400px;
        padding: 32px 36px;
        border-radius: var(--radius-2xl);
        box-shadow:
          0 1px 2px rgba(0,0,0,0.03),
          0 12px 32px rgba(0,0,0,0.08),
          0 32px 64px rgba(0,0,0,0.12);
      }


      .form-header {
        margin-bottom: 24px;
      }

      .form-title {
        font-size: 1.375rem;
      }

      .form-subtitle {
        font-size: 0.8125rem;
      }

      .error-box {
        padding: 10px 14px;
        font-size: 0.8125rem;
      }

      .field {
        margin-bottom: 18px;
      }

      .field-input {
        height: 50px;
        font-size: 0.9375rem;
      }

      .remember-row {
        margin: 4px 0 24px;
      }

      .btn-submit {
        height: 50px;
        font-size: 0.9375rem;
      }

      .form-footer {
        margin-top: 20px;
        font-size: 0.6875rem;
      }
    }
  </style>
</head>

<body>

  <div class="login-root">
    {{-- Organic background shapes --}}
    <div class="bg-shape bg-shape-1"></div>
    <div class="bg-shape bg-shape-2"></div>
    <div class="bg-shape bg-shape-3"></div>

    {{-- Subtle route lines (desktop) --}}
    <svg class="route-line-desktop" style="display:none;" aria-hidden="true" width="100%" height="100%">
      <path d="M-50,200 Q150,120 300,250 T600,180 T900,280" />
      <path d="M-30,450 Q200,380 350,480 T700,400 T1100,520" />
    </svg>

    {{-- Brand section above card --}}
    <div class="brand-section">
      <img src="{{ asset('images/logo-triguna-clean.png') }}" class="brand-logo" alt="Triguna Samudratrans">
      <div class="brand-name">Triguna Samudratrans</div>
      <div class="brand-tagline">Sistem Manajemen Kehadiran & Cuti</div>
    </div>

    {{-- Login card --}}
    <div class="form-card">
      <div class="card-accent"></div>

      <div class="form-header">
        <h1 class="form-title">Selamat Datang</h1>
        <p class="form-subtitle">Silakan masuk dengan akun karyawan Anda</p>
      </div>

      @if ($errors->any())
        <div class="error-box">
          <svg class="error-icon" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm0 12a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0-3a1 1 0 0 1-1-1V4a1 1 0 0 1 2 0v4a1 1 0 0 1-1 1z"/>
          </svg>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('login.store') }}" id="loginForm">
        @csrf

        <div class="field">
          <label class="field-label" for="username">Email</label>
          <input
            id="username"
            type="text"
            name="username"
            class="field-input @error('username') input-error @enderror"
            placeholder="email@perusahaan.com"
            autocomplete="username"
            value="{{ old('username') }}"
            autofocus>
        </div>

        <div class="field">
          <label class="field-label" for="password">Password</label>
          <div class="input-wrap">
            <input
              id="password"
              type="password"
              name="password"
              class="field-input @error('password') input-error @enderror"
              placeholder="Masukkan password"
              autocomplete="current-password">
            <button
              type="button"
              class="password-toggle"
              id="togglePassword"
              aria-label="Tampilkan password"
              title="Tampilkan password">
              {{-- Eye icon --}}
              <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              {{-- Eye-off icon (hidden by default) --}}
              <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="remember-row">
          <label class="custom-checkbox">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <span class="checkbox-mark">
              <svg viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.5 5.5L4.5 8.5L10.5 1.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="checkbox-label">Ingat saya</span>
          </label>
        </div>

        <button type="submit" class="btn-submit" id="btnSubmit">
          <span class="spinner" id="btnSpinner" style="display:none;"></span>
          <span class="btn-text" id="btnText">Masuk</span>
        </button>
      </form>

      <div class="form-footer">
        &copy; {{ date('Y') }} <strong>HRD Triguna Samudratrans</strong><br>
        Sistem Manajemen Sumber Daya Manusia
      </div>
    </div>
  </div>

  <script>
    (function() {
      // Show route lines on desktop
      var svg = document.querySelector('.route-line-desktop');
      if (svg && window.innerWidth >= 1024) {
        svg.style.display = 'block';
      }
      window.addEventListener('resize', function() {
        if (svg) {
          svg.style.display = window.innerWidth >= 1024 ? 'block' : 'none';
        }
      });

      // Toggle password visibility
      var toggleBtn = document.getElementById('togglePassword');
      var passwordInput = document.getElementById('password');
      var eyeIcon = document.getElementById('eyeIcon');
      var eyeOffIcon = document.getElementById('eyeOffIcon');

      if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
          var isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
          toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
          toggleBtn.setAttribute('title', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
          if (eyeIcon) eyeIcon.style.display = isPassword ? 'none' : 'block';
          if (eyeOffIcon) eyeOffIcon.style.display = isPassword ? 'block' : 'none';
        });
      }

      // Loading state on form submit
      var form = document.getElementById('loginForm');
      var btnSubmit = document.getElementById('btnSubmit');
      var btnSpinner = document.getElementById('btnSpinner');
      var btnText = document.getElementById('btnText');

      if (form && btnSubmit) {
        form.addEventListener('submit', function() {
          btnSubmit.disabled = true;
          btnSubmit.classList.add('is-loading');
          if (btnSpinner) btnSpinner.style.display = 'block';
          if (btnText) btnText.textContent = 'Memuat...';
        });
      }
    })();
  </script>

  {{-- Service Worker Registration for PWA --}}
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
          .then(function(registration) {
            console.log('SW registered:', registration.scope);
          })
          .catch(function(error) {
            console.log('SW registration failed:', error);
          });
      });
    }
  </script>

</body>
</html>
