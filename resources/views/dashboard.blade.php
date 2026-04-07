<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body{margin:0;font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f9fafb;color:#111827;}
        header{background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:5;box-shadow:0 1px 6px rgba(0,0,0,.05);}
        .brand{font-size:1.2rem;font-weight:800;letter-spacing:.2px;}
        nav a{margin-left:16px;text-decoration:none;color:#374151;font-weight:600;}
        .container{max-width:1020px;margin:20px auto;padding:0 16px;}
        .hero{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;box-shadow:0 6px 20px rgba(0,0,0,.03);}
        .hero h1{margin:0;font-size:1.8rem;}
        .hero p{margin:8px 0 0;color:#4b5563;}
        .grid{margin-top:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;box-shadow:0 4px 10px rgba(0,0,0,.03);}
        .card h3{margin:0;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;}
        .card p{margin:.4rem 0 0;font-size:1.25rem;font-weight:700;color:#111827;}
        .section{margin-top:18px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,.04);}
        .section h2{margin:0;font-size:1.1rem;}
        .section p{margin:.6rem 0 0;color:#4b5563;}
        .button{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:8px 12px;font-weight:700;cursor:pointer;margin-top:10px;}
    </style>
</head>
<body>
    <header>
        <div class="brand">Student Portal</div>
        <nav>
            <a href="#">Dashboard</a>
            <a href="#">Courses</a>
            <a href="#">Tasks</a>
            <a href="#">Profile</a>
        </nav>
    </header>
    <main class="container">
        <div class="hero">
            <h1>Welcome, {{ $name }}</h1>
            <p>Here is your dashboard snapshot. You can access your current classes and tasks below.</p>
            
            <!-- Sign Out Form - POST to /logout to clear session -->
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="button">Sign Out</button>
            </form>
        </div>
    </main>
</body>
</html>
