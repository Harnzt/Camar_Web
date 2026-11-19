@extends('main_page.admin-panel.layout')

@section('title', 'Verifikasi Akun')
@section('page-title', 'Verifikasi Akun & Dokumen')

@section('content')
<section class="panel">
    {{-- Filter role dihapus: halaman ini hanya berisi akun pembeli. --}}
    <form method="GET" class="filter-bar">
        <label class="filter-field filter-field-grow">
            <span>Cari</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau perusahaan">
        </label>
        <label class="filter-field">
            <span>Status</span>
            <select name="status">
                <option value="">Semua status</option>
                @foreach(['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak', 'suspended' => 'Dinonaktifkan'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn btn-primary">Terapkan</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Pengguna</th><th>Role</th><th>Dokumen</th><th>Status</th><th>Terdaftar</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><div class="user-cell"><img src="{{ $user->profile_photo_url }}" alt="" width="38" height="38"><div><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></div></div></td>
                    <td>{{ $user->role_label }}<small>{{ $user->category_label }}</small></td>
                    <td>{{ $user->document_verifications_count }} dokumen<small>{{ $user->pending_documents_count }} menunggu</small></td>
                    <td><span class="status {{ $user->status }}">{{ $user->status_label }}</span></td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td><a class="btn btn-sm btn-light" href="{{ route('admin.users.show', $user) }}">Periksa</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">
                    @if(request()->filled('search') || request()->filled('status'))
                        Tidak ada akun yang cocok dengan filter ini. <a href="{{ route('admin.users.index') }}">Reset filter</a>.
                    @else
                        Belum ada pembeli yang mendaftar. Akun baru akan muncul di sini untuk diverifikasi.
                    @endif
                </div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</section>
@endsection
