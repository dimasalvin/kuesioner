<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kuesioner Klinik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal: #2BBFA4; --teal-dark: #1E9A87; --teal-light: #E6F9F5;
            --text: #1A2B3C; --muted: #7A90A8; --border: #E2E8F0;
            --surface: #fff; --bg: #F7F9FC;
            --coral: #FF6B6B;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #C8EDE7 0%, #D4E9F7 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .card {
            background: var(--surface); border-radius: 24px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.12);
            width: 100%; max-width: 400px; padding: 40px 36px;
        }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--teal), var(--teal-dark));
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px; font-size: 28px;
            box-shadow: 0 8px 20px rgba(43,191,164,0.35);
        }
        .logo h1 { font-family: 'Caveat', cursive; font-size: 26px; color: var(--text); }
        .logo p  { font-size: 13px; color: var(--muted); margin-top: 4px; }

        .field { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 800; color: var(--muted);
                text-transform: uppercase; letter-spacing: .06em; margin-bottom: 7px; }
        input {
            width: 100%; padding: 13px 16px; border: 2px solid var(--border);
            border-radius: 10px; font-size: 15px; font-family: 'Nunito', sans-serif;
            color: var(--text); background: var(--bg); transition: border-color .2s, box-shadow .2s;
        }
        input:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(43,191,164,.12); }
        input.error { border-color: var(--coral); }

        .error-msg { font-size: 12px; color: var(--coral); margin-top: 5px; font-weight: 600; }

        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .remember input[type=checkbox] { width: auto; margin: 0; accent-color: var(--teal); }
        .remember span { font-size: 13px; color: var(--muted); }

        .btn {
            width: 100%; padding: 15px; background: linear-gradient(135deg, var(--teal), var(--teal-dark));
            color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 800;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            box-shadow: 0 4px 16px rgba(43,191,164,.4); transition: all .2s;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(43,191,164,.5); }

        .hint {
            margin-top: 28px; padding: 16px; background: var(--teal-light);
            border-radius: 10px; font-size: 12px; color: var(--teal-dark); line-height: 1.8;
        }
        .hint strong { display: block; margin-bottom: 4px; font-weight: 800; }
        .divider { height: 1px; background: var(--border); margin: 8px 0; }

        .back-link { display: block; text-align: center; margin-top: 20px;
                     font-size: 13px; color: var(--muted); text-decoration: none; }
        .back-link:hover { color: var(--teal); }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon">🏥</div>
        <h1>Dashboard Klinik</h1>
        <p>Sistem Kuesioner Kepuasan Pasien</p>
    </div>

    @if($errors->any())
        <div class="error-msg" style="margin-bottom:16px; padding:12px; background:#FFF0F0; border-radius:8px; border:1px solid var(--coral)">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="{{ $errors->has('email') ? 'error' : '' }}"
                   placeholder="nama@klinik.com" required autofocus>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="remember">
            <input type="checkbox" name="remember" id="remember">
            <span>Ingat saya</span>
        </div>
        <button type="submit" class="btn">Masuk Dashboard</button>
    </form>

    <div class="hint">
        <strong>Akun Demo:</strong>
        admin@klinik.com / admin123<br>
        management@klinik.com / mgmt123<br>
        <div class="divider"></div>
        andi.susanto@klinik.com / dokter123<br>
        ani.wulandari@klinik.com / perawat123
    </div>

    <a href="{{ route('kuesioner.index') }}" class="back-link">← Kembali ke halaman kuesioner</a>
</div>
</body>
</html>
