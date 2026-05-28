<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <style>
        :root{--primary:#4f46e5;--primary-dark:#3730a3;--surface:#fff;--text:#111827;--muted:#6b7280;--border:#d1d5db;--danger-bg:#fee2e2;--danger-border:#fecaca;--danger-text:#991b1b;--success-bg:#dcfce7;--success-border:#86efac;--success-text:#166534;}
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:
            radial-gradient(circle at 15% 20%, rgba(79,70,229,.16) 0%, transparent 30%),
            radial-gradient(circle at 85% 10%, rgba(37,99,235,.2) 0%, transparent 35%),
            linear-gradient(180deg, #eef2ff 0%, #f8fafc 55%, #eef2ff 100%);
            display:flex;align-items:center;justify-content:center;color:var(--text);padding:20px;}
        .card{width:min(520px,95vw);background:rgba(255,255,255,.96);border-radius:18px;box-shadow:0 18px 36px rgba(15,23,42,.18);overflow:hidden;border:1px solid #e2e8f0;}
        .top{background:linear-gradient(125deg,#4f46e5,#2563eb);color:#fff;padding:22px 24px;}
        .top h1{font-size:1.4rem;margin:0 0 6px;}
        .top p{margin:0;opacity:.9;font-size:.92rem;line-height:1.5;}
        .body{padding:22px 24px 24px;}
        .alert{border-radius:10px;padding:10px 12px;font-size:.85rem;margin-bottom:12px;border:1px solid transparent;}
        .alert-danger{background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-text);}
        .alert-success{background:var(--success-bg);border-color:var(--success-border);color:var(--success-text);}
        .field{margin-top:14px;}
        label{font-size:.8rem;color:#4b5563;margin-bottom:6px;display:block;font-weight:600;}
        input[type="email"]{width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--border);font-size:.92rem;}
        input[type="email"]:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.15);}
        .btn{width:100%;padding:11px;font-weight:700;border:none;border-radius:10px;background:linear-gradient(90deg,#4f46e5,#2563eb);color:#fff;font-size:.95rem;cursor:pointer;margin-top:18px;}
        .btn:hover{background:linear-gradient(90deg,var(--primary-dark),#1d4ed8);}
        .back{display:inline-block;margin-top:14px;color:#2563eb;text-decoration:none;font-weight:600;}
        .back:hover{text-decoration:underline;}
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <h1>Lupa Password?</h1>
            <p>Masukkan email akunmu. Kami akan mengirim link untuk reset password agar kamu bisa masuk lagi.</p>
        </div>
        <div class="body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                </div>
                <button type="submit" class="btn">Kirim Link Reset</button>
            </form>

            <a href="{{ route('login') }}" class="back">← Kembali ke login</a>
        </div>
    </div>
</body>
</html>
