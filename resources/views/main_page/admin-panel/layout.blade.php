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
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <img src="{{ asset('images/logo.png') }}" alt="CAMAR">
            <div><strong>CAMAR</strong><span>Administration</span></div>
        </a>

        <nav class="admin-nav">
            @if(Auth::user()->hasPermission('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i><span>Dashboard</span>
                </a>
            @endif
            @if(Auth::user()->hasPermission('users.verify'))
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i><span>Verifikasi Akun</span>
                </a>
            @endif
            @if(Auth::user()->hasPermission('projects.verify'))
                <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                    <i class="fas fa-seedling"></i><span>Verifikasi Proyek</span>
                </a>
            @endif
            @if(Auth::user()->hasPermission('transactions.manage'))
                <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i><span>Transaksi</span>
                </a>
            @endif

            @if(Auth::user()->isSuperAdmin())
                <div class="admin-nav-label">Super Admin</div>
                <a href="{{ route('admin.admins.index') }}" class="{{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i><span>Kelola Admin</span>
                </a>
                <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-key"></i><span>Role & Permission</span>
                </a>
            @endif
            @if(Auth::user()->hasPermission('audit.view'))
                <a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left"></i><span>Audit Log</span>
                </a>
            @endif
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-profile">
                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                <div><strong>{{ Auth::user()->name }}</strong><span>{{ Auth::user()->role_label }}</span></div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="admin-eyebrow">CAMAR Control Center</span>
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
            <a href="{{ route('home') }}" class="btn btn-light"><i class="fas fa-arrow-up-right-from-square"></i> Lihat Situs</a>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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
</body>
</html>
