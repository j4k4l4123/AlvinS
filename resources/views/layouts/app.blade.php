<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PerpusKu')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .top-navbar .navbar-actions {
            margin-left: auto !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            position: relative !important;
            flex: 0 0 auto !important;
        }

        .top-navbar .notification-dropdown {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            flex: 0 0 auto !important;
        }

        .top-navbar .notification-btn {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 42px !important;
            min-width: 42px !important;
            height: 42px !important;
            padding: 0 !important;
            overflow: visible !important;
            white-space: nowrap !important;
            border-radius: 999px !important;
            transition: transform 0.22s ease, background 0.22s ease, box-shadow 0.22s ease !important;
        }

        .top-navbar .notification-btn:hover {
            background: rgba(255, 255, 255, 0.18) !important;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.08), 0 12px 30px rgba(6, 78, 59, 0.22) !important;
            transform: translateY(-1px) scale(1.06) !important;
        }

        .top-navbar .notification-btn[aria-expanded="true"] {
            background: rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.08), 0 14px 34px rgba(6, 78, 59, 0.24) !important;
        }

        .top-navbar .notification-menu {
            position: absolute !important;
            top: calc(100% + 14px) !important;
            right: -10px !important;
            left: auto !important;
            width: 420px !important;
            max-width: min(420px, calc(100vw - 24px)) !important;
            max-height: 460px !important;
            display: block !important;
            background: rgba(255, 255, 255, 0.99) !important;
            border: 1px solid rgba(52, 211, 153, 0.18) !important;
            border-radius: 22px !important;
            box-shadow: 0 28px 90px rgba(6, 78, 59, 0.18) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: translate3d(0, -10px, 0) scale(0.92) !important;
            transform-origin: calc(100% - 28px) -14px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            z-index: 9999 !important;
            transition: opacity 0.24s ease, transform 0.24s cubic-bezier(0.22, 1, 0.36, 1), visibility 0.24s ease !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }

        .top-navbar .notification-menu.show {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translate3d(0, 0, 0) scale(1) !important;
        }

        .top-navbar .notification-item,
        .top-navbar .notification-menu-header,
        .top-navbar .notification-menu-footer,
        .top-navbar .notification-empty {
            display: block !important;
            white-space: normal !important;
            background: #ffffff !important;
        }

        .top-navbar .notification-menu-header {
            padding: 18px 20px 14px !important;
            border-bottom: 1px solid rgba(52, 211, 153, 0.12) !important;
            border-radius: 22px 22px 0 0 !important;
            font-size: 15px !important;
            letter-spacing: 0.01em !important;
            color: #064e3b !important;
            font-weight: 800 !important;
            background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%) !important;
        }

        .top-navbar .notification-item {
            color: #064e3b !important;
            text-decoration: none !important;
            line-height: 1.5 !important;
            border-bottom: 1px solid rgba(52, 211, 153, 0.08) !important;
            transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease !important;
            padding: 16px 20px !important;
        }

        .top-navbar .notification-item:hover {
            background: #f0fdf4 !important;
            transform: translateX(3px) !important;
            box-shadow: inset 4px 0 0 rgba(52, 211, 153, 0.9) !important;
        }

        .top-navbar .notification-item strong,
        .top-navbar .notification-item span,
        .top-navbar .notification-item small {
            display: block !important;
            background: transparent !important;
        }

        .top-navbar .notification-item strong {
            font-size: 14px !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
            color: #064e3b !important;
        }

        .top-navbar .notification-item span {
            font-size: 13px !important;
            color: #475569 !important;
            margin-bottom: 8px !important;
        }

        .top-navbar .notification-item small {
            font-size: 11px !important;
            color: #64748b !important;
            letter-spacing: 0.01em !important;
        }

        .top-navbar .notification-empty {
            padding: 20px !important;
            color: #64748b !important;
            text-align: center !important;
        }

        .top-navbar .notification-menu-footer {
            padding: 14px 18px 16px !important;
            border-radius: 0 0 22px 22px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #065f46 !important;
            text-align: center !important;
            border-top: 1px solid rgba(52, 211, 153, 0.08) !important;
            background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%) !important;
            text-decoration: none !important;
        }

        .top-navbar .notification-menu-footer:hover {
            background: linear-gradient(180deg, #f8fffb 0%, #dcfce7 100%) !important;
        }
    </style>
</head>
<body class="@yield('body-class')">
    @php
        $user = auth()->user();
        $isLibrarian = $user?->isLibrarian();
        $isMember = $user?->isMember();
        $navbarNotifications = $navbarNotifications ?? collect();
        $notificationCount = $navbarNotifications->count();
    @endphp

    <nav class="top-navbar" id="topNavbar">
        <button class="hamburger-btn" onclick="toggleSidebar()" title="Toggle Sidebar">☰</button>
        <a href="{{ route('dashboard') }}" class="navbar-brand">📚 PerpusKu</a>
        @auth
            <div class="navbar-actions">
                @if($isMember)
                    <div class="notification-dropdown">
                        <button type="button" class="notification-btn" onclick="toggleNotifications(event)" title="Notifikasi" aria-label="Notifikasi" aria-expanded="false" aria-controls="notificationMenu">
                            <span aria-hidden="true">🔔</span>
                            @if($notificationCount > 0)
                                <span class="notification-badge">{{ $notificationCount }}</span>
                            @endif
                        </button>
                        <div class="notification-menu" id="notificationMenu">
                            <div class="notification-menu-header">Notifikasi</div>
                            @forelse($navbarNotifications as $notification)
                                <a href="{{ route('member.notifications') }}" class="notification-item">
                                    <strong>{{ $notification->title }}</strong>
                                    <span>{{ $notification->message }}</span>
                                    <small>{{ $notification->created_at?->diffForHumans() }}</small>
                                </a>
                            @empty
                                <div class="notification-empty">Belum ada notifikasi.</div>
                            @endforelse
                            <a href="{{ route('member.notifications') }}" class="notification-menu-footer">Lihat semua</a>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-cancel">Logout</button>
                </form>
            </div>
        @endauth
    </nav>

    <div class="app-wrapper" id="appWrapper">
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav" id="sidebarNav">
                @if($isLibrarian)
                    <li>
                        <a href="{{ route('account.show') }}" class="{{ request()->routeIs('account.show') ? 'active' : '' }}">
                            <span class="nav-icon">👤</span> <span class="nav-text">Akun Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" data-route="books" class="{{ request()->routeIs('books.*') ? 'active' : '' }}">
                            <span class="nav-icon">📖</span> <span class="nav-text">Buku</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('racks.index') }}" class="{{ request()->routeIs('racks.*') ? 'active' : '' }}">
                            <span class="nav-icon">🗂️</span> <span class="nav-text">Rak</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('anggota.index') }}" data-route="anggota" class="{{ request()->routeIs('anggota.*') ? 'active' : '' }}">
                            <span class="nav-icon">👤</span> <span class="nav-text">Anggota</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pinjam.index') }}" data-route="pinjam" class="{{ request()->routeIs('pinjam.*') ? 'active' : '' }}">
                            <span class="nav-icon">📚</span> <span class="nav-text">Peminjaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pengembalian.index') }}" data-route="pengembalian" class="{{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                            <span class="nav-icon">📥</span> <span class="nav-text">Pengembalian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('library-cards.index') }}" class="{{ request()->routeIs('library-cards.*') ? 'active' : '' }}">
                            <span class="nav-icon">🪪</span> <span class="nav-text">Kartu</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('membership-requests.index') }}" class="{{ request()->routeIs('membership-requests.*') || request()->routeIs('librarian-registration-requests.*') || request()->routeIs('renewal-requests.*') || request()->routeIs('reservations.*') ? 'active' : '' }}">
                            <span class="nav-icon">📝</span> <span class="nav-text">Pengajuan</span>
                        </a>
                    </li>
                @elseif($isMember)
                    <li>
                        <a href="{{ route('account.show') }}" class="{{ request()->routeIs('account.show') ? 'active' : '' }}">
                            <span class="nav-icon">👤</span> <span class="nav-text">Akun Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.dashboard') }}" class="{{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">🏠</span> <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.books.index') }}" class="{{ request()->routeIs('member.books.*') ? 'active' : '' }}">
                            <span class="nav-icon">📖</span> <span class="nav-text">Katalog</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.borrowings.index') }}" class="{{ request()->routeIs('member.borrowings.*') || request()->routeIs('member.history') ? 'active' : '' }}">
                            <span class="nav-icon">📚</span> <span class="nav-text">Peminjaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.fines') }}" class="{{ request()->routeIs('member.fines*') ? 'active' : '' }}">
                            <span class="nav-icon">💸</span> <span class="nav-text">Denda</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.library-card') }}" class="{{ request()->routeIs('member.library-card') ? 'active' : '' }}">
                            <span class="nav-icon">🪪</span> <span class="nav-text">Kartu Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.submissions') }}" class="{{ request()->routeIs('member.submissions') || request()->routeIs('member.cancel-membership') ? 'active' : '' }}">
                            <span class="nav-icon">📝</span> <span class="nav-text">Pengajuan</span>
                        </a>
                    </li>
                @endif
            </ul>
            <div class="sidebar-footer">
                <span class="nav-text">© PerpusKu v1.0</span>
            </div>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="content-card" id="contentCard">
                @if(session('success'))
                    <div class="flash-message success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="flash-message error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <div class="custom-cursor" id="customCursor"></div>

    <script>
        (function() {
            const cursor = document.getElementById('customCursor');
            if (!cursor) return;

            let mouseX = 0, mouseY = 0;
            let cursorX = 0, cursorY = 0;

            document.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });

            function animateCursor() {
                cursorX += (mouseX - cursorX) * 0.15;
                cursorY += (mouseY - cursorY) * 0.15;
                cursor.style.left = cursorX + 'px';
                cursor.style.top = cursorY + 'px';
                requestAnimationFrame(animateCursor);
            }
            animateCursor();

            function onEnter() { cursor.classList.add('hovering'); }
            function onLeave() { cursor.classList.remove('hovering'); }

            function updateHoverTargets() {
                const clickables = document.querySelectorAll('a, button, input, select, textarea, .hamburger-btn, .item-card, .btn-action, .btn-add, .btn-search, .btn-submit, .btn-cancel');
                clickables.forEach(function(el) {
                    el.removeEventListener('mouseenter', onEnter);
                    el.removeEventListener('mouseleave', onLeave);
                    el.addEventListener('mouseenter', onEnter);
                    el.addEventListener('mouseleave', onLeave);
                });
            }

            updateHoverTargets();
            window.addEventListener('spaContentUpdated', updateHoverTargets);
        })();

        (function() {
            function onMouseMove(e) {
                const card = e.currentTarget;
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -6;
                const rotateY = ((x - centerX) / centerX) * 6;

                card.style.transform = 'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale3d(1.02, 1.02, 1.02)';

                card.querySelectorAll('.tilt-layer').forEach(function(layer, i) {
                    const depth = (i + 1) * 12;
                    layer.style.transform = 'translateZ(' + depth + 'px)';
                });
            }

            function onMouseLeave(e) {
                const card = e.currentTarget;
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
                card.querySelectorAll('.tilt-layer').forEach(function(layer) {
                    layer.style.transform = 'translateZ(0)';
                });
            }

            function initCardTilt() {
                document.querySelectorAll('.item-card').forEach(function(card) {
                    card.removeEventListener('mousemove', onMouseMove);
                    card.removeEventListener('mouseleave', onMouseLeave);
                    card.addEventListener('mousemove', onMouseMove);
                    card.addEventListener('mouseleave', onMouseLeave);
                });
            }

            initCardTilt();
            window.addEventListener('spaContentUpdated', initCardTilt);
        })();

        (function() {
            function onMove(e) {
                const btn = e.currentTarget;
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = 'translate(' + (x * 0.25) + 'px, ' + (y * 0.25) + 'px)';
            }

            function onLeave(e) {
                e.currentTarget.style.transform = 'translate(0, 0)';
            }

            function initMagneticButtons() {
                document.querySelectorAll('.btn-action, .btn-add, .btn-search, .btn-submit, .btn-cancel, .btn-back, .btn-return, .hamburger-btn').forEach(function(btn) {
                    btn.removeEventListener('mousemove', onMove);
                    btn.removeEventListener('mouseleave', onLeave);
                    btn.addEventListener('mousemove', onMove);
                    btn.addEventListener('mouseleave', onLeave);
                });
            }

            initMagneticButtons();
            window.addEventListener('spaContentUpdated', initMagneticButtons);
        })();

        function toggleSidebar() {
            const wrapper = document.getElementById('appWrapper');
            wrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', wrapper.classList.contains('sidebar-collapsed'));
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('appWrapper').classList.add('sidebar-collapsed');
        }

        function toggleNotifications(event) {
            if (event) {
                event.stopPropagation();
            }

            const menu = document.getElementById('notificationMenu');
            const button = document.querySelector('.notification-btn');
            if (!menu || !button) return;

            const isOpen = menu.classList.toggle('show');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('notificationMenu');
            const dropdown = document.querySelector('.notification-dropdown');

            if (!menu || !dropdown) return;

            if (!dropdown.contains(event.target)) {
                menu.classList.remove('show');
                const button = document.querySelector('.notification-btn');
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            }
        });
    </script>
</body>
</html>
