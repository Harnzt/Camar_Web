@extends('main_page.admin-panel.layout')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log Administratif')

@section('content')
<section class="panel">
    <form method="GET" class="filter-bar">
        <input name="action" value="{{ request('action') }}" placeholder="Filter jenis tindakan">
        <button class="btn btn-primary">Terapkan</button>
    </form>

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
                @forelse($logs as $log)
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

    {{ $logs->links() }}
</section>
@endsection

