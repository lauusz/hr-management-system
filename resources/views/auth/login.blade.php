<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login HRD</title>
  <style>
    :root {
      --navy: #1e4a8d;
      --bg: #f6f7fb;
      --text: #333;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    body {
      font-family: system-ui, Arial, sans-serif;
      background: var(--bg);
      margin: 0;
      color: var(--text);
    }

    .wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .card {
      background: #fff;
      padding: 28px 24px;
      width: 100%;
      max-width: 380px;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2 {
      margin-top: 0;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 700;
      color: var(--navy);
      font-size: 20px;
    }

    label {
      display: block;
      font-size: 14px;
      color: #444;
      margin-bottom: 6px;
    }

    input[type=text],
    input[type=password] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d0d0d0;
      border-radius: 8px;
      font-size: 15px;
      transition: border 0.2s, box-shadow 0.2s;
      background: #fff;
      display: block;
    }

    input[type=text]:focus,
    input[type=password]:focus {
      outline: none;
      border-color: var(--navy);
      box-shadow: 0 0 0 2px rgba(30, 74, 141, 0.15);
    }

    .row {
      margin-bottom: 16px;
    }

    .remember {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
    }

    .remember input[type=checkbox] {
      width: 16px;
      height: 16px;
      accent-color: var(--navy);
    }

    .btn {
      width: 100%;
      padding: 12px;
      border: 0;
      border-radius: 8px;
      background: var(--navy);
      color: #fff;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .btn:hover {
      background: #163a70;
    }

    .err {
      background: #ffe9e9;
      color: #a40000;
      padding: 10px 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
      line-height: 1.4;
    }

    @media (max-width: 480px) {
      .card {
        padding: 22px 18px;
      }
      h2 { font-size: 18px; }
      input, .btn { font-size: 14px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" method="POST" action="{{ route('login.store') }}">
      @csrf
      <h2>Login HRD</h2>

      @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
      @endif

      <div class="row">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus>
      </div>

      <div class="row">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
      </div>

      <div class="remember">
        <input id="remember" type="checkbox" name="remember">
        <label for="remember" style="margin:0;">Ingat saya</label>
      </div>

      <button class="btn" type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>
