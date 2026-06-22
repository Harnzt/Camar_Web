@extends('main_page.admin-panel.layout')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log Administratif')

@section('content')
<section class="panel">
    <form method="GET" class="filter-bar">
        <input name="action" value="{{ request('action') }}" placeholder="Filter jenis tindakan">
        <button class="btn btn-primary">Terapkan</button>
    </form>
    <div class="audit-list">
        @forelse($logs as $log)
            <article class="audit-card">
                <div class="audit-time"><strong>{{ $log->created_at->format('d M Y') }}</strong><span>{{ $log->created_at->format('H:i:s') }}</span></div>
                <div class="grow"><span class="panel-kicker">{{ $log->action }}</span><h3>{{ $log->description }}</h3><p>{{ $log->admin?->name ?? 'Sistem' }} · {{ $log->ip_address ?? '-' }}</p></div>
                @if($log->old_values || $log->new_values)
                    <details><summary>Lihat perubahan</summary><pre>{{ json_encode(['sebelum' => $log->old_values, 'sesudah' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>
                @endif
            </article>
        @empty
            <div class="empty-state">Belum ada aktivitas administratif.</div>
        @endforelse
    </div>
    {{ $logs->links() }}
</section>
@endsection
