<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice Login</title>
    <style>
        :root {
            --bg: #eef5fb;
            --card: #ffffff;
            --text: #0f2233;
            --muted: #5f6f7f;
            --primary: #0c7a6a;
            --border: #d7e2ed;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 18px;
            background: radial-gradient(circle at 10% 0, #d9ecff, transparent 42%), var(--bg);
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
        }
        .card {
            width: min(430px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 16px 34px rgba(15, 34, 51, .12);
        }
        h1 { margin: 0 0 6px; font-size: 1.2rem; }
        p { margin: 0 0 16px; color: var(--muted); font-size: .92rem; }
        label { display: block; margin-bottom: 10px; font-weight: 600; font-size: .88rem; }
        input {
            width: 100%;
            margin-top: 6px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            font: inherit;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 12px 0 14px;
            font-size: .85rem;
            color: var(--muted);
        }
        .remember input { width: auto; margin: 0; }
        button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            margin-bottom: 12px;
            background: #fff1f1;
            color: #9c1f1f;
            border: 1px solid #f1cccc;
            border-radius: 10px;
            padding: 10px;
            font-size: .88rem;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Ingreso Backoffice</h1>
    <p>Acceso operativo por tenant para equipo de campo y gerencia.</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('backoffice.login.store') }}">
        @csrf
        <label>
            Tenant
            <input type="text" name="tenant" value="{{ old('tenant', $defaultTenant) }}" required autocomplete="organization">
        </label>

        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </label>

        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <label class="remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Mantener sesión
        </label>

        <button type="submit">Entrar al Backoffice</button>
    </form>
</div>
</body>
</html>
