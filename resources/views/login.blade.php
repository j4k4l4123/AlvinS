<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login</title>
    <style>
        :root{--primary:#4f46e5;--surface:#fff;--text:#111827;--muted:#6b7280;}
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:
            radial-gradient(circle at 15% 20%, rgba(79,70,229,.16) 0%, transparent 30%),
            radial-gradient(circle at 85% 10%, rgba(37,99,235,.2) 0%, transparent 35%),
            linear-gradient(180deg, #eef2ff 0%, #f8fafc 55%, #eef2ff 100%);
            display:flex;align-items:center;justify-content:center;color:var(--text);}
        .card{width:min(510px,95vw);background:rgba(255,255,255,.96);backdrop-filter:blur(2px);border-radius:18px;box-shadow:0 18px 36px rgba(15,23,42,.18);overflow:hidden;border:1px solid #e2e8f0;}
        .top{background:linear-gradient(125deg,#4f46e5,#2563eb);color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;}
        .top .badge{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;background:rgba(255,255,255,.2);color:#fff;padding:4px 10px;border-radius:999px;}
        .top h1{font-size:1.45rem;margin:0;}
        .body{padding:20px 24px;}
        .note{font-size:.9rem;color:#4b5563;margin-bottom:14px;}
        .field{margin-top:12px;}
        label{font-size:.8rem;color:#4b5563;margin-bottom:5px;display:block;}
        input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #d1d5db;font-size:.9rem;}
        input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.15);}
        .btn{width:100%;padding:11px;font-weight:700;border:none;border-radius:10px;background:linear-gradient(90deg,#4f46e5,#2563eb);color:#fff;font-size:.95rem;cursor:pointer;margin-top:16px;}
        .extra{margin-top:12px;font-size:.82rem;color:#6b7280;text-align:center;}
        .links{margin-top:18px;display:flex;justify-content:space-between;font-size:.78rem;color:#2563eb;}
        .links a{text-decoration:none;color:#2563eb;}
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
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" placeholder="you@school.com" required autofocus></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" placeholder="********" required></div>
                <button type="submit" class="btn">Continue</button>
            </form>
            <div class="links"><a href="#">Need help?</a><a href="#">Privacy</a></div>
            <div class="extra">Tip: You can enter any values to access the dashboard.</div>
        </div>
    </div>
</body>
</html>