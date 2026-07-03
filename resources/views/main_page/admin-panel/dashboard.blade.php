@extends('main_page.admin-panel.layout')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
@php
    $canVerifyUsers = Auth::user()->hasPermission('users.verify');
    $canVerifyProjects = Auth::user()->hasPermission('projects.verify');
    $canManageAccounts = Auth::user()->hasPermission('admins.manage');
    $canViewAudit = Auth::user()->hasPermission('audit.view');
    $managedAccountLabel = Auth::user()->isSuperAdmin() ? 'Admin' : 'Auditor';
@endphp

<div class="stat-grid">
    @if($canVerifyUsers)
        <a class="stat-card" href="{{ route('admin.users.index', ['status' => 'pending']) }}">
            <i class="fas fa-user-clock orange"></i><div><span>Akun Menunggu</span><strong>{{ $stats['pending_users'] }}</strong></div>
        </a>
        <a class="stat-card" href="{{ route('admin.users.index') }}">
            <i class="fas fa-file-circle-check blue"></i><div><span>Dokumen Menunggu</span><strong>{{ $stats['pending_documents'] }}</strong></div>
        </a>
    @endif

    @if($canVerifyProjects)
        <a class="stat-card" href="{{ route('admin.projects.index', ['status' => 'pending']) }}">
            <i class="fas fa-seedling green"></i><div><span>Proyek Menunggu</span><strong>{{ $stats['pending_projects'] }}</strong></div>
        </a>
    @endif

    @if($canManageAccounts)
        <a class="stat-card" href="{{ route('admin.admins.index') }}">
            <i class="fas fa-user-shield green"></i><div><span>{{ $managedAccountLabel }} Aktif</span><strong>{{ $stats['active_managed_accounts'] }}</strong></div>
        </a>
        <a class="stat-card" href="{{ route('admin.admins.index') }}">
            <i class="fas fa-users blue"></i><div><span>Total {{ $managedAccountLabel }}</span><strong>{{ $stats['managed_accounts'] }}</strong></div>
        </a>
    @endif

    @if($canViewAudit)
        <a class="stat-card" href="{{ route('admin.audit.index') }}">
            <i class="fas fa-clock-rotate-left purple"></i><div><span>Aktivitas Tercatat</span><strong>{{ $stats['audit_logs'] }}</strong></div>
        </a>
    @endif
</div>

@if($canVerifyUsers || $canVerifyProjects)
    <div class="admin-grid {{ $canVerifyUsers && $canVerifyProjects ? 'two' : '' }}">
        @if($canVerifyUsers)
            <section class="panel">
                <div class="panel-heading"><div><span class="panel-kicker">Antrean terbaru</span><h2>Akun pengguna</h2></div><a href="{{ route('admin.users.index') }}">Lihat semua</a></div>
                <div class="list-stack">
                    @forelse($recentUsers as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="list-item">
                            <img src="{{ $user->profile_photo_url }}" alt="">
                            <div class="grow"><strong>{{ $user->name }}</strong><span>{{ $user->email }} - {{ $user->role_label }}</span></div>
                            <span class="status {{ $user->status }}">{{ $user->status_label }}</span>
                        </a>
                    @empty
                        <div class="empty-state">Belum ada pengguna.</div>
                    @endforelse
                </div>
            </section>
        @endif

        @if($canVerifyProjects)
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
        @endif
    </div>
@endif

@if($canManageAccounts)
    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Akses internal</span><h2>Akun {{ $managedAccountLabel }}</h2></div><a href="{{ route('admin.admins.index') }}">Kelola semua</a></div>
        <div class="list-stack">
            @forelse($recentManagedAccounts as $account)
                <a href="{{ route('admin.admins.index', ['search' => $account->email]) }}" class="list-item">
                    <img src="{{ $account->profile_photo_url }}" alt="">
                    <div class="grow"><strong>{{ $account->name }}</strong><span>{{ $account->email }} - {{ $account->role_label }}</span></div>
                    <span class="status {{ $account->status }}">{{ $account->status_label }}</span>
                </a>
            @empty
                <div class="empty-state">Belum ada akun {{ strtolower($managedAccountLabel) }}.</div>
            @endforelse
        </div>
    </section>
@endif

@if($canViewAudit)
<section class="panel">
    <div class="panel-heading"><div><span class="panel-kicker">Jejak tindakan</span><h2>Aktivitas administratif</h2></div><a href="{{ route('admin.audit.index') }}">Audit lengkap</a></div>
    <div class="table-wrap">
        <table class="table-hover">
            <thead>
                <tr>
                    <th style="width: 120px;">Waktu</th>
                    <th style="width: 180px;">Pelaku</th>
                    <th style="width: 200px;">Tindakan</th>
                    <th>Deskripsi</th>
                    <th style="width: 250px;">Detail Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td>
                            <strong>{{ $log->created_at->format('d M Y') }}</strong>
                            <small>{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            <strong>{{ $log->admin?->name ?? 'Sistem' }}</strong>
                            <small>{{ $log->ip_address ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="panel-kicker">{{ $log->action }}</span>
                        </td>
                        <td>
                            {{ $log->description }}
                        </td>
                        <td>
                            @if($log->old_values || $log->new_values)
                                <details class="table-log-details">
                                    <summary>Lihat perubahan</summary>
                                    <pre>{{ json_encode(['sebelum' => $log->old_values, 'sesudah' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                <span class="muted" style="font-size: 12px; color: var(--muted);">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">Belum ada aktivitas administratif.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection
