<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>HRD Triguna</title>

  <meta name="theme-color" content="#1e4a8d">
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" href="{{ asset('pwa/icon-180.png') }}">

  <style>
    :root {
      --navy: #1e4a8d;
      --bg: #f6f7fb;
      --text: #333;
    }

    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px 16px env(safe-area-inset-bottom);
    }

    .card {
      width: 100%;
      max-width: 380px;
      background: #fff;
      border-radius: 18px;
      padding: 26px 20px 28px;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
      animation: fadeUp .25s ease;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(12px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h2 {
      margin: 0 0 22px;
      text-align: center;
      font-size: 20px;
      font-weight: 700;
      color: var(--navy);
      letter-spacing: .3px;
    }

    .row {
      margin-bottom: 16px;
    }

    label {
      display: block;
      font-size: 13px;
      margin-bottom: 6px;
      color: #444;
    }

    input[type=text],
    input[type=password] {
      width: 100%;
      height: 46px;
      padding: 0 14px;
      font-size: 15px;
      border-radius: 10px;
      border: 1px solid #d0d0d0;
      background: #fff;
      transition: border .2s, box-shadow .2s;
    }

    input:focus {
      outline: none;
      border-color: var(--navy);
      box-shadow: 0 0 0 2px rgba(30, 74, 141, .15);
    }

    .remember {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 18px 0 22px;
      font-size: 14px;
    }

    .remember input {
      width: 18px;
      height: 18px;
      accent-color: var(--navy);
    }

    .btn {
      width: 100%;
      height: 48px;
      border-radius: 12px;
      border: none;
      background: var(--navy);
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s ease, transform .1s ease;
    }

    .btn:active {
      transform: scale(.98);
    }

    .btn:hover {
      background: #163a70;
    }

    .err {
      background: #ffe9e9;
      color: #a40000;
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 14px;
      line-height: 1.4;
      margin-bottom: 18px;
    }

    @media (max-width: 360px) {
      h2 {
        font-size: 18px;
      }
      .card {
        padding: 22px 16px 26px;
      }
    }
  </style>
</head>

<body>
  <div class="wrap">
    <form class="card" method="POST" action="{{ route('login.store') }}">
      @csrf

      <h2>HRD Triguna</h2>

      @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
      @endif

      <div class="row">
        <label for="username">Email</label>
        <input
          id="username"
          name="username"
          type="text"
          value="{{ old('username') }}"
          inputmode="email"
          autocomplete="username"
          autofocus>
      </div>

      <div class="row">
        <label for="password">Password</label>
        <input
          id="password"
          name="password"
          type="password"
          autocomplete="current-password"
          required>
      </div>

      <div class="remember">
        <input id="remember" type="checkbox" name="remember">
        <label for="remember" style="margin:0;">Ingat saya</label>
      </div>

      <button class="btn" type="submit">Masuk</button>
    </form>
  </div>

  <script>
    (function () {
      if (!('serviceWorker' in navigator)) return;

      const isLocalhost =
        location.hostname === 'localhost' ||
        location.hostname === '127.0.0.1';

      if (!(location.protocol === 'https:' || isLocalhost)) return;

      window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('service-worker.js') }}');
      });
    })();
  </script>
</body>

</html>
