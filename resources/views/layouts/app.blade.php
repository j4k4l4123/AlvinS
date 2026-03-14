<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'App')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(circle at 10% 20%, #2f6bff 0%, #1f4cc0 26%, #0f274f 100%); color: #0f172a; }
        .card { background: rgba(255,255,255,0.88); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.35); border-radius: 18px; box-shadow: 0 24px 60px rgba(0,0,0,.18); }
        .button { background: linear-gradient(135deg, #0f62fe, #5a56ff); color: white; border: none; border-radius: 10px; padding: 0.65rem 1rem; font-weight: 600; font-size: .95rem; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
        .button:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(15, 98, 254, 0.32); }
        .input{width:100%;padding:.65rem .75rem;border-radius:.65rem;border:1px solid rgba(15,23,42,.16);font-size:.95rem;outline:none;transition:border-color .2s ease,box-shadow .2s ease;}
        .input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12);}
        .text-muted { color: #475569; }
        .badge { display: inline-flex; align-items: center; gap: .3rem; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: .5rem; color: #3730a3; padding:.2rem .55rem; font-size:.8rem; }
    </style>
</head>
<body>
    <header style="position:fixed; top:0; left:0; right:0; background:rgba(15,23,42,0.9); color:#fff; backdrop-filter: blur(6px); border-bottom:1px solid rgba(255,255,255,0.08); z-index:20;">
        <div style="max-width:1100px; margin:0 auto; padding:.7rem 1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <div style="font-weight:800; letter-spacing:.05em; font-size:.95rem;">MyProfileApp</div>
            <nav style="display:flex; gap:.65rem; align-items:center;">
                <a href="{{ url('/') }}" style="color:#e2e8f0; text-decoration:none; font-weight:600;">Home</a>
                @auth
                    <a href="{{ url('/dashboard') }}" style="color:#e2e8f0; text-decoration:none; font-weight:600;">Dashboard</a>
                    <span style="color:#a5b4fc;">|</span>
                    <span style="font-size:.85rem; color:#cbd5e1;">{{ auth()->user()->name }}</span>
                @endauth
                @guest
                    <a href="{{ url('/login') }}" style="color:#f8fafc; text-decoration:none; font-weight:600;">Login</a>
                @endguest
            </nav>
        </div>
    </header>
    <main style="min-height:100vh; padding:5.2rem 1.2rem 1.2rem; display:flex; align-items:center; justify-content:center;">
        @yield('content')
    </main>
</body>
</html>