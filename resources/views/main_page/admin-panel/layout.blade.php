<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') - CAMAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Ubuntu:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}?v={{ filemtime(public_path('css/admin-panel.css')) }}">
    @stack('head')
</head>
<body>
@php
    $authUser = Auth::user();

    // Navigasi dikelompokkan: pekerjaan harian di atas, akses & keamanan di bawah.
    $navGroups = [
        'Operasional' => [
            ['show' => $authUser->hasPermission('admin.dashboard'), 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'fa-chart-pie', 'label' => 'Dashboard'],
            ['show' => $authUser->hasPermission('users.verify'), 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'fa-user-check', 'label' => 'Verifikasi Akun', 'badge' => ['users', 'nav-badge-users', 'akun menunggu']],
            ['show' => $authUser->hasPermission('projects.verify'), 'route' => 'admin.projects.index', 'match' => 'admin.projects.*', 'icon' => 'fa-seedling', 'label' => $authUser->canManageProjects() ? 'Kelola Proyek' : 'Verifikasi Proyek', 'badge' => ['projects', 'nav-badge-projects', $authUser->canReviewProjects() ? 'proyek menunggu verifikasi' : 'proyek perlu revisi']],
            ['show' => $authUser->hasPermission('transactions.view'), 'route' => 'admin.transactions.index', 'match' => 'admin.transactions.*', 'icon' => 'fa-receipt', 'label' => 'Transaksi'],
        ],
        'Akses & keamanan' => [
            ['show' => $authUser->hasPermission('admins.manage'), 'route' => 'admin.admins.index', 'match' => 'admin.admins.*', 'icon' => 'fa-user-shield', 'label' => $authUser->isSuperAdmin() ? 'Kelola Admin' : 'Kelola Auditor'],
            ['show' => $authUser->isSuperAdmin(), 'route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'icon' => 'fa-key', 'label' => 'Role & Permission'],
            ['show' => $authUser->hasPermission('audit.view'), 'route' => 'admin.audit.index', 'match' => 'admin.audit.*', 'icon' => 'fa-clock-rotate-left', 'label' => 'Audit Log'],
        ],
    ];
@endphp
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar-top">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <img src="{{ asset('images/logo.png') }}" alt="" width="42" height="42">
                <div><strong>CAMAR</strong><span>Panel administrasi</span></div>
            </a>
            <button type="button" class="admin-menu-toggle" id="admin-menu-toggle" aria-controls="admin-sidebar-menu" aria-expanded="false" aria-label="Buka menu">
                <i aria-hidden="true" class="fas fa-bars"></i>
            </button>
        </div>

        <div class="admin-sidebar-menu" id="admin-sidebar-menu">
            <nav class="admin-nav" aria-label="Menu panel admin">
                @foreach($navGroups as $groupLabel => $items)
                    @php
                        $visibleItems = array_filter($items, fn ($item) => $item['show']);
                    @endphp
                    @continue(empty($visibleItems))
                    <div class="admin-nav-label">{{ $groupLabel }}</div>
                    @foreach($visibleItems as $item)
                        @php
                            $isActive = request()->routeIs($item['match']);
                            $badgeCount = isset($item['badge']) ? $adminQueueCounts[$item['badge'][0]] : null;
                        @endphp
                        <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
                            <i aria-hidden="true" class="fas {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
                            @if($badgeCount !== null)
                                <span class="nav-badge" id="{{ $item['badge'][1] }}" data-count="{{ $badgeCount }}" @if($badgeCount === 0) hidden @endif>
                                    <span class="nav-badge-count">{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span><span class="visually-hidden"> {{ $item['badge'][2] }}</span>
                                </span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-profile">
                    <img src="{{ $authUser->profile_photo_url }}" alt="" width="38" height="38">
                    <div><strong>{{ $authUser->name }}</strong><span>{{ $authUser->role_label }}</span></div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"><i aria-hidden="true" class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="admin-eyebrow">Panel {{ $authUser->role_label }}</span>
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
            <a href="{{ route('home') }}" class="btn btn-light" aria-label="Lihat situs CAMAR"><i aria-hidden="true" class="fas fa-arrow-up-right-from-square"></i> <span class="hide-sm">Lihat Situs</span></a>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Tindakan belum dapat diproses.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<dialog class="admin-modal admin-modal-wide document-preview-modal" id="document-preview-modal" aria-labelledby="document-preview-title">
    <div class="admin-modal-card">
        <div class="admin-modal-header">
            <div><span class="panel-kicker">Pratinjau dokumen</span><h3 id="document-preview-title">Dokumen</h3></div>
            <button type="button" class="modal-close" data-close-preview aria-label="Tutup pratinjau"><i aria-hidden="true" class="fas fa-xmark"></i></button>
        </div>
        <div class="document-preview-body" id="document-preview-body"></div>
        <div class="admin-modal-footer">
            <a href="#" class="btn btn-light" id="document-preview-open" target="_blank" rel="noopener"><i aria-hidden="true" class="fas fa-up-right-from-square"></i> Buka di tab baru</a>
            <a href="#" class="btn btn-primary" id="document-preview-download"><i aria-hidden="true" class="fas fa-download"></i> Unduh</a>
        </div>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Menu sidebar pada layar kecil
    const sidebar = document.getElementById('admin-sidebar');
    const menuToggle = document.getElementById('admin-menu-toggle');
    const setMenuOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        menuToggle.innerHTML = `<i aria-hidden="true" class="fas fa-${open ? 'xmark' : 'bars'}"></i>`;
    };
    menuToggle?.addEventListener('click', () => setMenuOpen(!sidebar.classList.contains('is-open')));
    // Escape menutup menu mobile dan mengembalikan fokus ke tombol pemicunya.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            setMenuOpen(false);
            menuToggle.focus();
        }
    });

    // Pratinjau dokumen
    const previewModal = document.getElementById('document-preview-modal');
    const previewBody = document.getElementById('document-preview-body');
    const closePreview = () => {
        previewModal.close();
    };
    previewModal.addEventListener('close', () => { previewBody.innerHTML = ''; });
    previewModal.addEventListener('click', (event) => { if (event.target === previewModal) closePreview(); });
    previewModal.querySelector('[data-close-preview]').addEventListener('click', closePreview);

    document.querySelectorAll('[data-document-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            const { previewUrl, downloadUrl, previewKind, previewTitle } = button.dataset;
            document.getElementById('document-preview-title').textContent = previewTitle;
            document.getElementById('document-preview-open').href = previewUrl;
            document.getElementById('document-preview-download').href = downloadUrl;

            previewBody.innerHTML = '';
            if (previewKind === 'image') {
                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = previewTitle;
                previewBody.appendChild(image);
            } else {
                const frame = document.createElement('iframe');
                frame.src = previewUrl;
                frame.title = previewTitle;
                previewBody.appendChild(frame);
            }
            previewModal.showModal();
        });
    });
});
</script>

<!-- Realtime WebSockets (Laravel Echo + Reverb) -->
<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') {
            return;
        }

        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: window.location.hostname,
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            forceTLS: (window.location.protocol === 'https:'),
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '{{ url('/broadcasting/auth') }}',
        });

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'admin-toast';
            toast.setAttribute('role', 'status');
            toast.innerText = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function incrementCounter(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const next = (parseInt(el.dataset.count ?? el.innerText, 10) || 0) + 1;
            el.dataset.count = next;
            (el.querySelector('.nav-badge-count') ?? el).innerText = next > 99 ? '99+' : next;
            el.hidden = false;
        }

        window.Echo.private('admin-dashboard')
            .listen('.DashboardUpdated', (e) => {
                showToast(e.data.message);

                if (e.data.type === 'new_user') {
                    incrementCounter('stat-pending-users');
                    incrementCounter('nav-badge-users');
                }
            });
    });
</script>
@stack('scripts')
</body>
</html>
