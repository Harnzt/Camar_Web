@extends('main_page.admin-panel.layout')

@section('title', 'Kelola Proyek')
@section('page-title', 'Kelola Proyek')

@section('content')
<section class="panel">
    @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
            <a class="btn btn-primary" href="{{ route('admin.projects.create') }}"><i class="fas fa-plus"></i> Tambah Proyek</a>
        </div>
    @endif
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
            <thead><tr><th>Proyek</th><th>Pengembang / Perusahaan</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td><strong>{{ $project->name }}</strong><small>{{ $project->location ?? '-' }}</small></td>
                    <td>{{ $project->company_name }}</td>
                    <td>{{ $project->category }}</td>
                    <td>{{ number_format($project->stock_available) }} ton</td>
                    <td><span class="status {{ $project->verification_status }}">{{ str_replace('_', ' ', $project->verification_status) }}</span></td>
                    <td style="display:flex;gap:6px;">
                        <a class="btn btn-light btn-sm" href="{{ route('admin.projects.show', $project) }}">Detail</a>
                        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                            <a class="btn btn-light btn-sm" href="{{ route('admin.projects.edit', $project) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-light btn-sm" type="submit">Hapus</button>
                            </form>
                        @endif
                    </td>
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
