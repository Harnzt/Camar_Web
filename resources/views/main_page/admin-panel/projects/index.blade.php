@extends('main_page.admin-panel.layout')

@section('title', Auth::user()->canManageProjects() ? 'Kelola Proyek' : 'Daftar Proyek')
@section('page-title', Auth::user()->canManageProjects() ? 'Kelola Proyek' : 'Daftar Proyek')

@section('content')
<section class="panel">
    @if(Auth::user()->canManageProjects())
        <div class="panel-toolbar">
            <a class="btn btn-primary" href="{{ route('admin.projects.create') }}"><i aria-hidden="true" class="fas fa-plus"></i> Tambah Proyek</a>
        </div>
    @endif
    <form method="GET" class="filter-bar">
        <label class="filter-field filter-field-grow">
            <span>Cari</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama proyek, perusahaan, atau kategori">
        </label>
        <label class="filter-field">
            <span>Status</span>
            <select name="status">
                <option value="">Semua status</option>
                @foreach(['pending' => 'Menunggu auditor', 'approved' => 'Tayang', 'revision_required' => 'Perlu revisi', 'rejected' => 'Ditolak'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
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
                    <td><span class="status {{ $project->verification_status }}">{{ ['pending' => 'Menunggu auditor', 'approved' => 'Tayang', 'revision_required' => 'Perlu revisi', 'rejected' => 'Ditolak'][$project->verification_status] ?? $project->verification_status }}</span></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-light btn-sm" href="{{ route('admin.projects.show', $project) }}">Detail</a>
                            @if(Auth::user()->canManageProjects())
                                <a class="btn btn-light btn-sm" href="{{ route('admin.projects.edit', $project) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light btn-sm" type="submit">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">
                    @if(request()->filled('search') || request()->filled('status'))
                        Tidak ada proyek yang cocok dengan filter ini. <a href="{{ route('admin.projects.index') }}">Reset filter</a>.
                    @elseif(Auth::user()->canManageProjects())
                        Belum ada proyek. <a href="{{ route('admin.projects.create') }}">Tambah proyek pertama</a>.
                    @else
                        Belum ada proyek yang terdaftar.
                    @endif
                </div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $projects->links() }}
</section>
@endsection
