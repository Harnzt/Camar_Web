@extends('main_page.admin-panel.layout')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log Administratif')

@section('content')
@php
    $activeFilters = array_filter($filters, fn ($value) => filled($value));
@endphp
<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Jejak tindakan</h2>
            <p class="muted panel-note">{{ Auth::user()->isSuperAdmin() ? 'Menampilkan aktivitas semua role administrator.' : 'Menampilkan aktivitas akun auditor.' }}</p>
        </div>
        <a class="btn btn-light btn-sm" href="{{ route('admin.audit.export', $activeFilters) }}">
            <i aria-hidden="true" class="fas fa-file-csv"></i> Ekspor CSV
        </a>
    </div>

    <form method="GET" class="audit-filter">
        <label>
            <span>Pelaku</span>
            <select name="admin_id">
                <option value="">Semua pelaku</option>
                @foreach($actors as $actor)
                    <option value="{{ $actor->id }}" @selected((string) $filters['admin_id'] === (string) $actor->id)>
                        {{ $actor->name }} ({{ $actor->role_label }}){{ $actor->trashed() ? ' - dihapus' : '' }}
                    </option>
                @endforeach
            </select>
        </label>
        @if(count($roleOptions) > 1)
            <label>
                <span>Role pelaku</span>
                <select name="role">
                    <option value="">Semua role</option>
                    @foreach($roleOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <label>
            <span>Jenis target</span>
            <select name="target_type">
                <option value="">Semua target</option>
                @foreach($targetOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['target_type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>ID target</span>
            <input type="number" name="target_id" min="1" value="{{ $filters['target_id'] }}" placeholder="Contoh: 12">
        </label>
        <label>
            <span>Dari tanggal</span>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
        </label>
        <label>
            <span>Sampai tanggal</span>
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
        </label>
        <label class="audit-filter-wide">
            <span>Jenis tindakan</span>
            <input name="action" value="{{ $filters['action'] }}" placeholder="mis. certificate, admin.status, project">
        </label>
        <div class="audit-filter-actions">
            @if($activeFilters)
                <a href="{{ route('admin.audit.index') }}" class="btn btn-light">Reset</a>
            @endif
            <button class="btn btn-primary"><i aria-hidden="true" class="fas fa-filter"></i> Terapkan</button>
        </div>
    </form>

    <p class="muted audit-result-count">{{ number_format($logs->total(), 0, ',', '.') }} aktivitas ditemukan.</p>

    <div class="table-wrap">
        <table class="table-hover">
            <thead>
                <tr>
                    <th class="col-time">Waktu</th>
                    <th class="col-actor">Pelaku</th>
                    <th class="col-action">Tindakan</th>
                    <th class="col-target">Target</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <strong>{{ $log->created_at->format('d M Y') }}</strong>
                            <small>{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            <strong>{{ $log->admin?->name ?? 'Sistem' }}</strong>
                            <small>{{ $log->admin?->role_label ?? '-' }} · {{ $log->ip_address ?? '-' }}</small>
                        </td>
                        <td><code class="action-code">{{ $log->action }}</code></td>
                        <td>{{ $log->target_label }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                @if($activeFilters)
                                    Tidak ada aktivitas yang cocok dengan filter ini. <a href="{{ route('admin.audit.index') }}">Reset filter</a> untuk melihat semua aktivitas.
                                @else
                                    Belum ada aktivitas administratif yang tercatat.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection
