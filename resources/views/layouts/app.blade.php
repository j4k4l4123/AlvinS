<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="top-navbar" id="topNavbar">
        <button class="hamburger-btn" onclick="toggleSidebar()" title="Toggle Sidebar">☰</button>
        <div class="navbar-brand">📚 PerpusKu</div>
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

    <script>
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
            const routeOrder = ['/books', '/anggota', '/pinjam', '/pengembalian'];
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
                    if (normalizePath(a.getAttribute('href')) === normalized) {
                        a.classList.add('active');
                    }
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
                return {
                    content: newCard ? newCard.innerHTML : '',
                    title: newTitle ? newTitle.textContent : 'Book System'
                };
            }

            // Animate content transition
            function animateTransition(direction, url, newContent, newTitle) {
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
                    animateTransition(direction, url, data.content, data.title);
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
                                currentUrl = url;
                                updateActiveLink(newPath);
                                window.scrollTo(0, 0);
                                card.classList.remove('page-exit-up', 'page-exit-down');
                                card.classList.add(dir === 'up' ? 'page-enter-up' : 'page-enter-down');
                                reattachContentScripts();
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
</body>
</html>

