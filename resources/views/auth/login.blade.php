<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>HRD Triguna Samudratrans</title>

  <link rel="icon" href="{{ asset('images/logo-triguna-clean.png') }}" type="image/png">

  <style>
    :root {
      --primary: #3b82f6;
      --primary-dark: #1e3a8a;
      --primary-light: #93c5fd;
      --accent: #facc15;

      --gray-50: #f8fafc;
      --gray-200: #e2e8f0;
      --gray-400: #94a3b8;
      --gray-500: #64748b;
      --gray-600: #475569;
      --gray-900: #0f172a;

      --danger-bg: #fef2f2;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
      background: var(--gray-50);
      color: var(--gray-900);
      min-height: 100vh;
    }

    .login-root {
      display: flex;
      min-height: 100vh;
    }

    /* LEFT PANEL */
    .brand-panel {
      display: none;
      flex: 1;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 40%, #3b82f6 100%);
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
      background: rgba(255,255,255,0.08);
      border-radius: 50%;
      filter: blur(2px);
    }

    .brand-panel::after {
      content: '';
      position: absolute;
      bottom: -120px;
      left: -60px;
      width: 400px;
      height: 400px;
      background: rgba(255,255,255,0.05);
      border-radius: 50%;
      filter: blur(4px);
    }

    .brand-content {
      position: relative;
      z-index: 1;
      text-align: center;
      max-width: 380px;
    }

    .brand-logo img {
      width: 100%;
      max-width: 260px;
      margin-bottom: 24px;
    }

    .brand-name {
      font-size: 26px;
      font-weight: 800;
      color: #fff;
      text-shadow: 0 2px 10px rgba(0,0,0,0.15);
      margin-bottom: 12px;
    }

    .brand-divider {
      width: 40px;
      height: 3px;
      background: var(--accent);
      border-radius: 999px;
      margin: 20px auto;
    }

    .brand-tagline {
      font-size: 14px;
      color: rgba(255,255,255,0.75);
      line-height: 1.6;
    }

    /* RIGHT PANEL */
    .form-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 20px;
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

    /* MOBILE LOGO */
    .form-logo-mobile {
      display: none;
      justify-content: center;
      margin-bottom: 20px;
    }

    .form-logo-mobile img {
      width: 240px;
      height: auto;
    }

    .form-title {
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 4px;
    }

    .form-subtitle {
      font-size: 13.5px;
      color: var(--gray-500);
      margin-bottom: 20px;
    }

    .field {
      margin-bottom: 16px;
    }

    .field-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-600);
      margin-bottom: 6px;
      display: block;
      text-transform: uppercase;
    }

    .field-input {
      width: 100%;
      height: 48px;
      padding: 0 14px;
      border: 1.5px solid var(--gray-200);
      border-radius: 12px;
      background: #fff;
      font-size: 14px;
      transition: 0.2s;
    }

    .field-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
      outline: none;
    }

    .remember-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 14px 0 20px;
    }

    .custom-checkbox {
      position: relative;
      display: flex;
      align-items: center;
      cursor: pointer;
      user-select: none;
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
      border: 2px solid var(--gray-200);
      border-radius: 6px;
      background: #fff;
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
      font-size: 13.5px;
      color: var(--gray-600);
      font-weight: 500;
      margin-left: 4px;
    }

    .custom-checkbox input:checked ~ .checkbox-mark {
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      border-color: #2563eb;
      box-shadow: 0 2px 8px rgba(37,99,235,0.3);
    }

    .custom-checkbox input:checked ~ .checkbox-mark svg {
      opacity: 1;
      transform: scale(1);
    }

    .custom-checkbox input:focus-visible ~ .checkbox-mark {
      box-shadow: 0 0 0 3px rgba(59,130,246,0.3);
    }

    .btn-submit {
      width: 100%;
      height: 50px;
      border-radius: 12px;
      border: none;
      color: #fff;
      font-weight: 700;
      cursor: pointer;
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      transition: 0.2s;
    }

    .btn-submit:hover {
      background: linear-gradient(135deg, #1e3a8a, #2563eb);
      box-shadow: 0 6px 20px rgba(37,99,235,0.35);
    }

    .form-footer {
      margin-top: 20px;
      font-size: 12px;
      color: var(--gray-400);
      text-align: center;
    }

    /* DESKTOP */
    @media (min-width: 900px) {
      .brand-panel { display: flex; }
    }

    /* MOBILE */
    @media (max-width: 599px) {
      .brand-panel { display: none; }

      body {
        background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
      }

      .form-logo-mobile {
        display: flex;
      }

      .form-title,
      .form-subtitle {
        text-align: center;
      }
    }

  </style>
</head>

<body>

<div class="login-root">

  <!-- LEFT -->
  <div class="brand-panel">
    <div class="brand-content">
      <div class="brand-logo">
        <img src="{{ asset('images/triguna-logo.png') }}">
      </div>
      <div class="brand-name">HRD Triguna<br>Samudratrans</div>
      <div class="brand-divider"></div>
      <div class="brand-tagline">
        Sistem Manajemen Kehadiran & Cuti Berbasis Digital
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="form-panel">
    <div class="form-card">

      <!-- MOBILE LOGO -->
      <div class="form-logo-mobile">
        <img src="{{ asset('images/triguna-logo.png') }}">
      </div>

      <h1 class="form-title">Masuk</h1>
      <p class="form-subtitle">Gunakan alamat email yang terdaftar</p>

      @if ($errors->any())
      <div style="background:#fef2f2;padding:10px;border-radius:8px;margin-bottom:10px;color:#991b1b;">
        {{ $errors->first() }}
      </div>
      @endif

      <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
          <label class="field-label">Email</label>
          <input type="text" name="username" class="field-input" placeholder="nama@example.com">
        </div>

        <div class="field">
          <label class="field-label">Password</label>
          <input type="password" name="password" class="field-input" placeholder="••••••••">
        </div>

        <div class="remember-row">
          <label class="custom-checkbox">
            <input type="checkbox" name="remember">
            <span class="checkbox-mark">
              <svg viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.5 5.5L4.5 8.5L10.5 1.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="checkbox-label">Ingat saya</span>
          </label>
        </div>

        <button class="btn-submit">Masuk</button>
      </form>

      <div class="form-footer">
        &copy; {{ date('Y') }} HRD Triguna Samudratrans
      </div>

    </div>
  </div>

</div>

</body>
</html>