@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="width:min(100%, 980px);">
    <div class="card" style="padding:1rem; margin-bottom:1rem; border-radius:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.75rem; flex-wrap:wrap;">
            <div>
                <div style="font-size:.75rem; letter-spacing:.08em; font-weight:700; color:#4f46e5; text-transform:uppercase;">Personal Profile</div>
                <h1 style="margin:.35rem 0 0; font-size:2rem; line-height:1.2;">Hi, {{ auth()->user()->name }} 👋</h1>
                <p class="text-muted" style="margin:.35rem 0 0;">Your personal dashboard with contact, skills, and experience.</p>
            </div>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="button" style="background:#ef4444;">Logout</button>
            </form>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
        <div class="card" style="padding:1rem;">
            <div style="display:flex; align-items:center; gap:.7rem;">
                <div style="width:56px; height:56px; border-radius:50%; background:#4f46e5; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div style="font-size:1.1rem; font-weight:700;">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:.9rem; margin-top:.2rem;">Full-Stack Developer</div>
                </div>
            </div>
            <div style="margin-top:1rem; border-top:1px dashed #e2e8f0; padding-top:.8rem;">
                <div style="font-weight:600; margin-bottom:.35rem;">About me</div>
                <p class="text-muted" style="margin:0; font-size:.95rem; line-height:1.45;">I'm a passionate developer building clean, scalable web apps with Laravel and modern JavaScript. I love solving real user problems with simple interfaces.</p>
            </div>
            <div style="margin-top:1rem; display:grid; grid-template-columns:1fr 1fr; gap:.5rem;">
                <div style="background:#f8fafc; border-radius:.55rem; border:1px solid #e2e8f0; padding:.55rem; font-size:.9rem;"><strong>📧 Email</strong><br><span class="text-muted" style="font-size:.85rem;">{{ auth()->user()->email }}</span></div>
                <div style="background:#f8fafc; border-radius:.55rem; border:1px solid #e2e8f0; padding:.55rem; font-size:.9rem;"><strong>📍 Location</strong><br><span class="text-muted" style="font-size:.85rem;">Jakarta, Indonesia</span></div>
            </div>
            <div style="margin-top:1rem;"><strong>📱 Contact</strong><br><span class="text-muted" style="font-size:.9rem;">+62 812-3456-7890 | linkedin.com/in/yourname</span></div>
        </div>

        <div class="card" style="padding:1rem; display:flex; flex-direction:column; gap:.6rem;">
            <div><strong>Skills</strong></div>
            <div style="display:flex; flex-wrap:wrap; gap:.4rem;">
                <span class="badge">Laravel</span>
                <span class="badge">PHP</span>
                <span class="badge">Livewire</span>
                <span class="badge">Vue.js</span>
                <span class="badge">Tailwind</span>
                <span class="badge">SQL</span>
            </div>
            <div style="margin-top:.6rem;"><strong>Experience</strong></div>
            <div style="padding:.55rem; background:#f8fafc; border-radius:.7rem; border:1px solid #e2e8f0;">
                <div style="font-weight:700;">Senior Web Developer</div>
                <div style="font-size:.85rem; color:#64748b;">Acme Digital · 2022 - Present</div>
                <p class="text-muted" style="margin:.35rem 0 0; font-size:.9rem;">Built internal dashboards and customer portals used by 50K+ monthly active users.</p>
            </div>
            <div style="padding:.55rem; background:#f8fafc; border-radius:.7rem; border:1px solid #e2e8f0;">
                <div style="font-weight:700;">Frontend Engineer</div>
                <div style="font-size:.85rem; color:#64748b;">Nova Studio · 2019 - 2022</div>
                <p class="text-muted" style="margin:.35rem 0 0; font-size:.9rem;">Designed component libraries and improved app performance by 28%.</p>
            </div>
        </div>
    </div>

    <div class="card" style="padding:1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem;"><div style="font-weight:700;">Recent Projects</div><span class="badge">In progress</span></div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px,1fr)); gap:.7rem;">
            <div style="background:#fff; border-radius:.7rem; border:1px solid #e2e8f0; padding:.75rem;"><div style="font-weight:700;">TaskFlow ERP</div><div class="text-muted" style="font-size:.84rem; margin:.25rem 0;">Enterprise planning system</div><div style="font-size:.75rem; color:#0f172a;">Status: <strong style="color:#0f62fe;">Design review</strong></div></div>
            <div style="background:#fff; border-radius:.7rem; border:1px solid #e2e8f0; padding:.75rem;"><div style="font-weight:700;">LaunchBoard CRM</div><div class="text-muted" style="font-size:.84rem; margin:.25rem 0;">Customer pipeline dashboard</div><div style="font-size:.75rem; color:#0f172a;">Status: <strong style="color:#059669;">Shipped</strong></div></div>
            <div style="background:#fff; border-radius:.7rem; border:1px solid #e2e8f0; padding:.75rem;"><div style="font-weight:700;">TeamSync App</div><div class="text-muted" style="font-size:.84rem; margin:.25rem 0;">Collaboration workflow mobile app</div><div style="font-size:.75rem; color:#0f172a;">Status: <strong style="color:#f97316;">Development</strong></div></div>
        </div>
    </div>
</div>
@endsection