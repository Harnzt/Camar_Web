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

{{-- Kartu antrean (perlu tindakan) memakai aksen kuning; kartu informasi memakai warna merek. --}}
<div class="stat-grid">
    @if($canVerifyUsers)
        <a class="stat-card" href="{{ route('admin.users.index', ['status' => 'pending']) }}">
            <i aria-hidden="true" class="fas fa-user-clock stat-icon is-queue"></i>
            <div><span>Akun menunggu verifikasi</span><strong id="stat-pending-users">{{ $stats['pending_users'] }}</strong></div>
        </a>
        <a class="stat-card" href="{{ route('admin.users.index', ['status' => 'pending']) }}">
            <i aria-hidden="true" class="fas fa-file-circle-check stat-icon is-queue"></i>
            <div><span>Dokumen akun menunggu</span><strong id="stat-pending-documents">{{ $stats['pending_documents'] }}</strong></div>
        </a>
    @endif

    @if(Auth::user()->canReviewProjects())
        <a class="stat-card" href="{{ route('admin.projects.index', ['status' => 'pending']) }}">
            <i aria-hidden="true" class="fas fa-seedling stat-icon is-queue"></i>
            <div><span>Proyek menunggu verifikasi</span><strong id="stat-pending-projects">{{ $stats['pending_projects'] }}</strong></div>
        </a>
    @elseif(Auth::user()->canManageProjects())
        <a class="stat-card" href="{{ route('admin.projects.index', ['status' => 'revision_required']) }}">
            <i aria-hidden="true" class="fas fa-pen-to-square stat-icon is-queue"></i>
            <div><span>Proyek perlu revisi</span><strong>{{ $stats['revision_projects'] }}</strong></div>
        </a>
        <a class="stat-card" href="{{ route('admin.projects.index', ['status' => 'pending']) }}">
            <i aria-hidden="true" class="fas fa-hourglass-half stat-icon"></i>
            <div><span>Menunggu auditor</span><strong>{{ $stats['pending_projects'] }}</strong></div>
        </a>
    @endif

    @if($canManageAccounts)
        <a class="stat-card" href="{{ route('admin.admins.index') }}">
            <i aria-hidden="true" class="fas fa-user-shield stat-icon"></i>
            <div>
                <span>Akun {{ strtolower($managedAccountLabel) }} aktif</span>
                <strong>{{ $stats['active_managed_accounts'] }} <small>dari {{ $stats['managed_accounts'] }}</small></strong>
            </div>
        </a>
    @endif
</div>

@if($salesChart)
    @php
        $chartTiles = [
            ['key' => 'transactions', 'title' => 'Transaksi lunas per bulan', 'total' => number_format($salesChart['totals']['transactions'], 0, ',', '.'), 'unit' => 'transaksi'],
            ['key' => 'volume', 'title' => 'Ton CO₂e terjual per bulan', 'total' => number_format($salesChart['totals']['volume'], 0, ',', '.'), 'unit' => 'ton'],
            ['key' => 'revenue', 'title' => 'Pendapatan per bulan', 'total' => 'Rp '.number_format($salesChart['totals']['revenue'], 0, ',', '.'), 'unit' => ''],
        ];
        $hasSales = $salesChart['totals']['transactions'] > 0;
    @endphp
    <section class="panel">
        <div class="panel-heading">
            <h2>Penjualan 12 bulan terakhir</h2>
            @if(Auth::user()->hasAnyPermission('transactions.view', 'certificates.issue'))
                <a href="{{ route('admin.transactions.index') }}">Lihat transaksi</a>
            @endif
        </div>
        <p class="muted panel-note">Dihitung dari transaksi yang sudah dibayar lewat Midtrans (lunas, terverifikasi, dan bersertifikat).</p>

        @if(! $hasSales)
            <div class="empty-state">Belum ada transaksi lunas dalam 12 bulan terakhir. Grafik akan terisi setelah pembayaran pertama berhasil.</div>
        @else
            <div class="chart-grid">
                @foreach($chartTiles as $tile)
                    <figure class="chart-card">
                        <figcaption>
                            <span>{{ $tile['title'] }}</span>
                            <strong>{{ $tile['total'] }} <small>{{ $tile['unit'] }}</small></strong>
                        </figcaption>
                        <div class="chart-canvas">
                            <canvas id="chart-{{ $tile['key'] }}" role="img" aria-label="{{ $tile['title'] }}, 12 bulan terakhir. Data lengkap ada di tabel di bawah."></canvas>
                            <p class="chart-fallback" hidden>Grafik gagal dimuat. Data lengkap tetap tersedia di tabel di bawah.</p>
                        </div>
                    </figure>
                @endforeach
            </div>
        @endif
        <details class="chart-table">
            <summary>Lihat data dalam tabel</summary>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Bulan</th><th>Transaksi</th><th>Volume (ton)</th><th>Pendapatan</th></tr></thead>
                    <tbody>
                        @foreach($salesChart['labels'] as $index => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ number_format($salesChart['transactions'][$index], 0, ',', '.') }}</td>
                                <td>{{ number_format($salesChart['volume'][$index], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($salesChart['revenue'][$index], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </section>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                // CDN gagal: sembunyikan kanvas kosong dan arahkan ke tabel data.
                document.querySelectorAll('.chart-canvas').forEach((box) => {
                    box.querySelector('canvas')?.remove();
                    box.querySelector('.chart-fallback')?.removeAttribute('hidden');
                });
                return;
            }

            const data = @json($salesChart);
            const ink = { muted: '#5d6d7e', grid: '#edf1f4', bar: '#26667f', barHover: '#123f6d' };
            const integer = new Intl.NumberFormat('id-ID');
            const compact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 });
            const rupiah = (value) => 'Rp ' + integer.format(Math.round(value));

            Chart.defaults.font.family = 'Ubuntu, sans-serif';
            Chart.defaults.color = ink.muted;

            const series = {
                transactions: { format: (v) => integer.format(v) + ' transaksi', tick: (v) => integer.format(v) },
                volume: { format: (v) => integer.format(v) + ' ton', tick: (v) => compact.format(v) },
                revenue: { format: rupiah, tick: (v) => compact.format(v) },
            };

            Object.entries(series).forEach(([key, cfg]) => {
                const canvas = document.getElementById('chart-' + key);
                if (!canvas) return;

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data[key],
                            backgroundColor: ink.bar,
                            hoverBackgroundColor: ink.barHover,
                            borderRadius: 4,
                            borderSkipped: 'start',
                            maxBarThickness: 24,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        // Dial MOTION 1: tanpa animasi masuk, hanya respons hover.
                        animation: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#18342a',
                                padding: 10,
                                displayColors: false,
                                callbacks: { label: (ctx) => cfg.format(ctx.parsed.y) },
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6, font: { size: 11 } } },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: ink.grid },
                                ticks: { precision: 0, maxTicksLimit: 5, font: { size: 11 }, callback: cfg.tick },
                            },
                        },
                    },
                });
            });
        });
        </script>
    @endpush
@endif

@if($canVerifyUsers || $canVerifyProjects)
    <div class="admin-grid {{ $canVerifyUsers && $canVerifyProjects ? 'two' : '' }}">
        @if($canVerifyUsers)
            <section class="panel">
                <div class="panel-heading"><h2>Pendaftar terbaru</h2><a href="{{ route('admin.users.index') }}">Lihat semua</a></div>
                <div class="list-stack">
                    @forelse($recentUsers as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="list-item">
                            <img src="{{ $user->profile_photo_url }}" alt="" width="38" height="38">
                            <div class="grow"><strong>{{ $user->name }}</strong><span>{{ $user->email }} · {{ $user->role_label }}</span></div>
                            <span class="status {{ $user->status }}">{{ $user->status_label }}</span>
                        </a>
                    @empty
                        <div class="empty-state">Belum ada pembeli yang mendaftar.</div>
                    @endforelse
                </div>
            </section>
        @endif

        @if($canVerifyProjects)
            <section class="panel">
                <div class="panel-heading"><h2>Proyek terbaru</h2><a href="{{ route('admin.projects.index') }}">Lihat semua</a></div>
                <div class="list-stack">
                    @forelse($recentProjects as $project)
                        <a href="{{ route('admin.projects.show', $project) }}" class="list-item">
                            <div class="list-icon"><i aria-hidden="true" class="fas fa-leaf"></i></div>
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
        <div class="panel-heading"><h2>Akun {{ $managedAccountLabel }}</h2><a href="{{ route('admin.admins.index') }}">Kelola semua</a></div>
        <div class="list-stack">
            @forelse($recentManagedAccounts as $account)
                <a href="{{ route('admin.admins.index', ['search' => $account->email]) }}" class="list-item">
                    <img src="{{ $account->profile_photo_url }}" alt="" width="38" height="38">
                    <div class="grow"><strong>{{ $account->name }}</strong><span>{{ $account->email }} · {{ $account->role_label }}</span></div>
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
    <div class="panel-heading"><h2>Aktivitas administratif terbaru</h2><a href="{{ route('admin.audit.index') }}">Audit lengkap</a></div>
    <div class="table-wrap">
        <table class="table-hover">
            <thead>
                <tr>
                    <th class="col-time">Waktu</th>
                    <th class="col-actor">Pelaku</th>
                    <th class="col-action">Tindakan</th>
                    <th>Deskripsi</th>
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
                        <td><code class="action-code">{{ $log->action }}</code></td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">Belum ada aktivitas administratif yang tercatat.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endif
@endsection
