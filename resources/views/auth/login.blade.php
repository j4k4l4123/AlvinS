@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="card" style="width:min(100%, 920px); padding: .5rem;">
    <div style="display:grid;grid-template-columns:1fr 1fr; gap:1rem;">
        <div style="padding:1.4rem; display:flex; flex-direction:column; justify-content:center;">
            <h1 style="font-size:2rem; margin:0; color:#0f172a;">Welcome back!</h1>
            <p class="text-muted" style="margin:.5rem 0 1.4rem;">Login to your dashboard and continue building your project.</p>
           
        </div>
        <div style="background:#ffffff; border-radius:14px; padding:1rem;">
            <h2 style="margin:0 0 .8rem; font-size:1.2rem;">Sign in</h2>

            @if ($errors->any())
                <div style="margin-bottom:.9rem; padding:.7rem; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:.6rem;">
                    <ul style="margin:0; padding-left:1.2rem;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" style="display:flex; flex-direction:column; gap:.72rem;">
                @csrf
                <div>
                    <label for="email" style="font-size:.85rem; font-weight:600; color:#334155;">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input" placeholder="you@example.com" />
                </div>
                <div>
                    <label for="password" style="font-size:.85rem; font-weight:600; color:#334155;">Password</label>
                    <input id="password" name="password" type="password" required class="input" placeholder="••••••••" />
                </div>
                <div style="display:flex; align-items:center; gap:.4rem;">
                    <input id="remember" name="remember" type="checkbox" style="width:.9rem; height:.9rem;" />
                    <label for="remember" style="font-size:.85rem; color:#475569;">Remember me</label>
                </div>
                <button type="submit" class="button">Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection