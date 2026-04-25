<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class')">
    <nav class="top-navbar" id="topNavbar">
        <button class="hamburger-btn" onclick="toggleSidebar()" title="Toggle Sidebar">☰</button>
        <a href="{{ route('dashboard') }}" class="navbar-brand">📚 PerpusKu</a>
    </nav>

    <div class="app-wrapper" id="appWrapper">
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav" id="sidebarNav">
                <li>
                    <a href="{{ route('books.index') }}" data-route="books" class="{{ request()->routeIs('books.*') ? 'active' : '' }}">
                        <span class="nav-icon">📖</span> <span class="nav-text">Buku</span>
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
        // ===== Custom Cursor =====
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

        // ===== 3D Card Tilt Effect =====
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

        // ===== Magnetic Button Effect =====
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

        // ===== Sidebar Toggle =====
        function toggleSidebar() {
            const wrapper = document.getElementById('appWrapper');
            wrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', wrapper.classList.contains('sidebar-collapsed'));
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('appWrapper').classList.add('sidebar-collapsed');
        }

        // ===== SPA Navigation System =====
        (function() {
            const routeOrder = ['/dashboard', '/books', '/anggota', '/pinjam', '/pengembalian'];
            let navigating = false;
            let currentUrl = window.location.href;

            function normalizePath(p) {
                return p.replace(/\/$/, '');
            }

            function getRouteIndex(path) {
                return routeOrder.indexOf(normalizePath(path));
            }

            function updateActiveLink(path) {
                const normalized = normalizePath(path);
                document.querySelectorAll('#sidebarNav a').forEach(function(a) {
                    a.classList.remove('active');
                    try {
                        const linkUrl = new URL(a.getAttribute('href'), window.location.href);
                        if (normalizePath(linkUrl.pathname) === normalized) {
                            a.classList.add('active');
                        }
                    } catch (_) {}
                });
            }

            // Fetch page and extract content
            async function fetchPage(url) {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Failed to load');
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.querySelector('#contentCard');
                const newTitle = doc.querySelector('title');
                const newBody = doc.querySelector('body');
                return {
                    content: newCard ? newCard.innerHTML : '',
                    title: newTitle ? newTitle.textContent : 'Book System',
                    bodyClass: newBody ? newBody.className : ''
                };
            }

            // Animate content transition
            function animateTransition(direction, url, newContent, newTitle, newBodyClass) {
                if (navigating) return;
                navigating = true;

                const card = document.getElementById('contentCard');
                const exitClass = direction === 'up' ? 'page-exit-up' : 'page-exit-down';
                const enterClass = direction === 'up' ? 'page-enter-up' : 'page-enter-down';

                // Exit animation
                card.classList.add(exitClass);

                setTimeout(function() {
                    // Swap content
                    card.innerHTML = newContent;
                    document.title = newTitle;
                    document.body.className = newBodyClass;
                    history.pushState({ url: url }, newTitle, url);
                    currentUrl = url;
                    updateActiveLink(new URL(url).pathname);

                    // Reset scroll
                    window.scrollTo(0, 0);

                    // Remove exit class, add enter class
                    card.classList.remove(exitClass);
                    card.classList.add(enterClass);

                    // Re-attach form handlers and inline scripts in new content
                    reattachContentScripts();

                    // Dispatch event for new effects to reinitialize
                    window.dispatchEvent(new Event('spaContentUpdated'));

                    // Clean up enter animation
                    setTimeout(function() {
                        card.classList.remove(enterClass);
                        navigating = false;
                        // Re-init scroll listeners for new page
                        initScrollNavigation();
                    }, 450);
                }, 380);
            }

            // Main navigation function
            async function navigateTo(url, direction) {
                if (navigating || url === currentUrl) return;
                try {
                    const data = await fetchPage(url);
                    animateTransition(direction, url, data.content, data.title, data.bodyClass);
                } catch (e) {
                    console.error('Navigation failed:', e);
                    window.location.href = url; // fallback
                }
            }

            // Re-attach event handlers to forms and links inside content
            function reattachContentScripts() {
                const card = document.getElementById('contentCard');

                // Intercept links inside content
                card.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        const href = link.getAttribute('href');
                        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                        if (link.target === '_blank') return;
                        if (e.ctrlKey || e.metaKey || e.shiftKey) return;

                        try {
                            const url = new URL(href, window.location.href);
                            if (url.origin !== window.location.origin) return;
                        } catch (_) { return; }

                        // Only intercept main route pages
                        const path = normalizePath(new URL(href, window.location.href).pathname);
                        if (!routeOrder.includes(path)) return;

                        e.preventDefault();
                        const currentIdx = getRouteIndex(window.location.pathname);
                        const targetIdx = routeOrder.indexOf(path);
                        const dir = (targetIdx < currentIdx) ? 'up' : 'down';
                        navigateTo(href, dir);
                    });
                });

                // Intercept form submissions (POST) — still need full reload for now
                // But for GET search forms, we can SPA-navigate
                card.querySelectorAll('form[method="GET"]').forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        const action = form.getAttribute('action') || window.location.href;
                        try {
                            const url = new URL(action, window.location.href);
                            if (url.origin !== window.location.origin) return;
                            const path = normalizePath(url.pathname);
                            if (!routeOrder.includes(path)) return;

                            e.preventDefault();
                            const params = new URLSearchParams(new FormData(form));
                            const fullUrl = action + (action.includes('?') ? '&' : '?') + params.toString();
                            navigateTo(fullUrl, 'down');
                        } catch (_) {}
                    });
                });
            }

            // ===== Scroll-based Navigation =====
            let scrollCleanup = null;

            function initScrollNavigation() {
                if (scrollCleanup) scrollCleanup();

                // Disable scroll navigation when search/filter params are present
                if (window.location.search) return;

                const currentPath = normalizePath(window.location.pathname);
                const currentIdx = getRouteIndex(currentPath);
                if (currentIdx === -1) return;

                const route = {
                    prev: currentIdx > 0 ? routeOrder[currentIdx - 1] : null,
                    next: currentIdx < routeOrder.length - 1 ? routeOrder[currentIdx + 1] : null
                };

                let lastScrollTop = 0;
                let edgeTimer = null;
                let triggered = false;

                function clearEdgeTimer() {
                    if (edgeTimer) {
                        clearTimeout(edgeTimer);
                        edgeTimer = null;
                    }
                }

                function checkNavigate(direction, url) {
                    if (navigating || triggered) return;
                    clearEdgeTimer();
                    edgeTimer = setTimeout(function() {
                        if (navigating || triggered) return;
                        triggered = true;
                        navigateTo(url, direction);
                    }, 180);
                }

                function onScroll() {
                    if (triggered || navigating) return;

                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight;
                    const clientHeight = window.innerHeight;
                    const scrollDirection = scrollTop > lastScrollTop ? 'down' : 'up';
                    lastScrollTop = scrollTop;

                    if (route.prev && scrollDirection === 'up' && scrollTop <= 5) {
                        checkNavigate('up', window.location.origin + route.prev);
                        return;
                    }

                    if (route.next && scrollDirection === 'down' && scrollTop + clientHeight >= scrollHeight - 5) {
                        checkNavigate('down', window.location.origin + route.next);
                        return;
                    }

                    if (scrollTop > 50 && scrollTop + clientHeight < scrollHeight - 50) {
                        clearEdgeTimer();
                    }
                }

                function onWheel(e) {
                    if (triggered || navigating) return;

                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight;
                    const clientHeight = window.innerHeight;

                    if (route.prev && e.deltaY < 0 && scrollTop <= 5) {
                        checkNavigate('up', window.location.origin + route.prev);
                        return;
                    }

                    if (route.next && e.deltaY > 0 && scrollTop + clientHeight >= scrollHeight - 5) {
                        checkNavigate('down', window.location.origin + route.next);
                        return;
                    }

                    if ((route.prev && e.deltaY > 0 && scrollTop <= 5) ||
                        (route.next && e.deltaY < 0 && scrollTop + clientHeight >= scrollHeight - 5)) {
                        clearEdgeTimer();
                    }
                }

                window.addEventListener('scroll', onScroll, { passive: true });
                window.addEventListener('wheel', onWheel, { passive: true });

                scrollCleanup = function() {
                    window.removeEventListener('scroll', onScroll);
                    window.removeEventListener('wheel', onWheel);
                    clearEdgeTimer();
                };
            }

            // ===== Intercept sidebar link clicks =====
            document.querySelectorAll('#sidebarNav a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = link.getAttribute('href');
                    const currentIdx = getRouteIndex(window.location.pathname);
                    const targetIdx = routeOrder.indexOf(normalizePath(new URL(href, window.location.href).pathname));
                    const dir = (targetIdx < currentIdx) ? 'up' : 'down';
                    navigateTo(href, dir);
                });
            });

            // ===== Intercept navbar logo click =====
            document.querySelectorAll('.navbar-brand').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');
                    if (!href) return;
                    if (e.ctrlKey || e.metaKey || e.shiftKey) return;

                    try {
                        const url = new URL(href, window.location.href);
                        if (url.origin !== window.location.origin) return;
                    } catch (_) { return; }

                    const path = normalizePath(new URL(href, window.location.href).pathname);
                    if (!routeOrder.includes(path)) return;

                    e.preventDefault();
                    const currentIdx = getRouteIndex(window.location.pathname);
                    const targetIdx = routeOrder.indexOf(path);
                    const dir = (targetIdx < currentIdx) ? 'up' : 'down';
                    navigateTo(href, dir);
                });
            });

            // ===== Handle browser back/forward =====
            window.addEventListener('popstate', function(e) {
                if (e.state && e.state.url) {
                    const url = e.state.url;
                    const currentPath = normalizePath(window.location.pathname);
                    const newPath = normalizePath(new URL(url).pathname);
                    const currentIdx = routeOrder.indexOf(currentPath);
                    const newIdx = routeOrder.indexOf(newPath);
                    const dir = (newIdx < currentIdx) ? 'up' : 'down';

                    // Don't use navigateTo (which pushes state), just fetch and swap
                    if (!navigating) {
                        navigating = true;
                        fetchPage(url).then(function(data) {
                            const card = document.getElementById('contentCard');
                            card.classList.add(dir === 'up' ? 'page-exit-up' : 'page-exit-down');
                            setTimeout(function() {
                                card.innerHTML = data.content;
                                document.title = data.title;
                                document.body.className = data.bodyClass;
                                currentUrl = url;
                                updateActiveLink(newPath);
                                window.scrollTo(0, 0);
                                card.classList.remove('page-exit-up', 'page-exit-down');
                                card.classList.add(dir === 'up' ? 'page-enter-up' : 'page-enter-down');
                                reattachContentScripts();
                                window.dispatchEvent(new Event('spaContentUpdated'));
                                setTimeout(function() {
                                    card.classList.remove('page-enter-up', 'page-enter-down');
                                    navigating = false;
                                    initScrollNavigation();
                                }, 450);
                            }, 380);
                        });
                    }
                }
            });

            // ===== Initialize =====
            reattachContentScripts();
            initScrollNavigation();

            // Store initial state
            history.replaceState({ url: window.location.href }, document.title, window.location.href);
            currentUrl = window.location.href;
        })();
    </script>
    @stack('scripts')
</body>
