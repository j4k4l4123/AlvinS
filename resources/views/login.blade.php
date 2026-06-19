<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login</title>
    <style>
        :root{--primary:#4f46e5;--primary-dark:#3730a3;--surface:#fff;--text:#111827;--muted:#6b7280;--border:#d1d5db;--danger-bg:#fee2e2;--danger-border:#fecaca;--danger-text:#991b1b;--success-bg:#dcfce7;--success-border:#86efac;--success-text:#166534;}
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:
            radial-gradient(circle at 15% 20%, rgba(79,70,229,.16) 0%, transparent 30%),
            radial-gradient(circle at 85% 10%, rgba(37,99,235,.2) 0%, transparent 35%),
            linear-gradient(180deg, #eef2ff 0%, #f8fafc 55%, #eef2ff 100%);
            display:flex;align-items:center;justify-content:center;color:var(--text);padding:20px;}
        .card{width:min(510px,95vw);background:rgba(255,255,255,.96);backdrop-filter:blur(2px);border-radius:18px;box-shadow:0 18px 36px rgba(15,23,42,.18);overflow:hidden;border:1px solid #e2e8f0;}
        .top{background:linear-gradient(125deg,#4f46e5,#2563eb);color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;}
        .top .badge{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;background:rgba(255,255,255,.2);color:#fff;padding:4px 10px;border-radius:999px;}
        .top h1{font-size:1.45rem;margin:0;}
        .body{padding:20px 24px 24px;}
        .note{font-size:.9rem;color:#4b5563;margin-bottom:14px;line-height:1.5;}
        .alert{border-radius:10px;padding:10px 12px;font-size:.85rem;margin-bottom:12px;border:1px solid transparent;}
        .alert-danger{background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-text);}
        .alert-success{background:var(--success-bg);border-color:var(--success-border);color:var(--success-text);}
        .field{margin-top:14px;}
        label{font-size:.8rem;color:#4b5563;margin-bottom:6px;display:block;font-weight:600;}
        input[type="email"],input[type="password"]{width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);font-size:.9rem;}
        input[type="email"]:focus,input[type="password"]:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.15);}
        .field-error{margin-top:6px;font-size:.78rem;color:var(--danger-text);}
        .options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;flex-wrap:wrap;}
        .remember{display:flex;align-items:center;gap:8px;font-size:.85rem;color:#374151;}
        .remember input{width:auto;margin:0;accent-color:var(--primary);}
        .auth-link,.register-link{color:#2563eb;text-decoration:none;font-weight:600;}
        .auth-link:hover,.register-link:hover{text-decoration:underline;}
        .btn{width:100%;padding:11px;font-weight:700;border:none;border-radius:10px;background:linear-gradient(90deg,#4f46e5,#2563eb);color:#fff;font-size:.95rem;cursor:pointer;margin-top:18px;transition:transform .15s ease, box-shadow .15s ease, background .15s ease;}
        .btn:hover{transform:translateY(-1px);box-shadow:0 10px 18px rgba(79,70,229,.18);background:linear-gradient(90deg,var(--primary-dark),#1d4ed8);}
        .register-wrap{margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;text-align:center;font-size:.85rem;color:var(--muted);}
        .extra{margin-top:12px;font-size:.82rem;color:#6b7280;text-align:center;line-height:1.5;}
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <div>
                <div style="font-size:.8rem;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">Student Portal</div>
                <h1>Quick Access</h1>
            </div>
            <div class="badge">Secure</div>
        </div>
        <div class="body">
            <p class="note">Login to your learning dashboard and view your tasks, progress, and schedule in one place.</p>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="you@school.com" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="********" required>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="options">
                    <label class="remember" for="remember">
                        <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn">Continue</button>
            </form>

            <div class="extra">Gunakan akun yang sudah terdaftar untuk masuk ke dashboard. Jika password baru saja direset, silakan login dengan password terbaru.</div>
        </div>
    </div>
</body>
</html>
