@extends('main_page.admin-panel.layout')

@section('title', 'Verifikasi Proyek')
@section('page-title', 'Verifikasi Proyek')

@section('content')
<section class="panel">
    <form method="GET" class="filter-bar">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari proyek, perusahaan, atau kategori">
        <select name="status">
            <option value="">Semua status</option>
            @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'revision_required' => 'Perlu Revisi', 'rejected' => 'Ditolak'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Terapkan</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Proyek</th><th>Seller</th><th>Kategori</th><th>Stok</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong><small>{{ $project->location ?? '-' }}</small></td>
                    <td>{{ $project->seller?->name ?? '-' }}<small>{{ $project->company_name }}</small></td>
                    <td>{{ $project->category }}</td>
                    <td>{{ number_format($project->stock_available) }} ton</td>
                    <td><span class="status {{ $project->verification_status }}">{{ str_replace('_', ' ', $project->verification_status) }}</span></td>
                    <td><a class="btn btn-light btn-sm" href="{{ route('admin.projects.show', $project) }}">Periksa</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">Tidak ada proyek yang sesuai filter.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $projects->links() }}
</section>
@endsection
