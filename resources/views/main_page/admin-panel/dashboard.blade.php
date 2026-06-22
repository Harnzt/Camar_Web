@extends('main_page.admin-panel.layout')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
<div class="stat-grid">
    <a class="stat-card" href="{{ route('admin.users.index', ['status' => 'pending']) }}">
        <i class="fas fa-user-clock orange"></i><div><span>Akun Menunggu</span><strong>{{ $stats['pending_users'] }}</strong></div>
    </a>
    <a class="stat-card" href="{{ route('admin.users.index') }}">
        <i class="fas fa-file-circle-check blue"></i><div><span>Dokumen Menunggu</span><strong>{{ $stats['pending_documents'] }}</strong></div>
    </a>
    <a class="stat-card" href="{{ route('admin.projects.index', ['status' => 'pending']) }}">
        <i class="fas fa-seedling green"></i><div><span>Proyek Menunggu</span><strong>{{ $stats['pending_projects'] }}</strong></div>
    </a>
    <a class="stat-card" href="{{ route('admin.transactions.index', ['status' => 'pending']) }}">
        <i class="fas fa-receipt purple"></i><div><span>Transaksi Pending</span><strong>{{ $stats['pending_orders'] }}</strong></div>
    </a>
</div>

<div class="admin-grid two">
    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Antrean terbaru</span><h2>Akun pengguna</h2></div><a href="{{ route('admin.users.index') }}">Lihat semua</a></div>
        <div class="list-stack">
            @forelse($recentUsers as $user)
                <a href="{{ route('admin.users.show', $user) }}" class="list-item">
                    <img src="{{ $user->profile_photo_url }}" alt="">
                    <div class="grow"><strong>{{ $user->name }}</strong><span>{{ $user->email }} · {{ $user->role_label }}</span></div>
                    <span class="status {{ $user->status }}">{{ $user->status_label }}</span>
                </a>
            @empty
                <div class="empty-state">Belum ada pengguna.</div>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Pengajuan terbaru</span><h2>Proyek karbon</h2></div><a href="{{ route('admin.projects.index') }}">Lihat semua</a></div>
        <div class="list-stack">
            @forelse($recentProjects as $project)
                <a href="{{ route('admin.projects.show', $project) }}" class="list-item">
                    <div class="list-icon"><i class="fas fa-leaf"></i></div>
                    <div class="grow"><strong>{{ $project->name }}</strong><span>{{ $project->seller?->name ?? $project->company_name }}</span></div>
                    <span class="status {{ $project->verification_status }}">{{ str_replace('_', ' ', $project->verification_status) }}</span>
                </a>
            @empty
                <div class="empty-state">Belum ada proyek.</div>
            @endforelse
        </div>
    </section>
</div>

@if(Auth::user()->hasPermission('audit.view'))
<section class="panel">
    <div class="panel-heading"><div><span class="panel-kicker">Jejak tindakan</span><h2>Aktivitas administratif</h2></div><a href="{{ route('admin.audit.index') }}">Audit lengkap</a></div>
    <div class="timeline">
        @forelse($recentLogs as $log)
            <div class="timeline-item">
                <span class="timeline-dot"></span>
                <div><strong>{{ $log->description }}</strong><span>{{ $log->admin?->name ?? 'Sistem' }} · {{ $log->created_at->diffForHumans() }}</span></div>
            </div>
        @empty
            <div class="empty-state">Belum ada aktivitas administratif.</div>
        @endforelse
    </div>
</section>
@endif
@endsection
