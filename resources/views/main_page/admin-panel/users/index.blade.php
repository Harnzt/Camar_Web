@extends('main_page.admin-panel.layout')

@section('title', 'Verifikasi Akun')
@section('page-title', 'Verifikasi Akun & Dokumen')

@section('content')
<section class="panel">
    <form method="GET" class="filter-bar">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau perusahaan">
        <select name="role">
            <option value="">Semua role</option>
            <option value="buyer" @selected(request('role') === 'buyer')>Buyer</option>
            <option value="seller" @selected(request('role') === 'seller')>Seller</option>
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach(['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak', 'suspended' => 'Dinonaktifkan'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Terapkan</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Pengguna</th><th>Role</th><th>Dokumen</th><th>Status</th><th>Terdaftar</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><div class="user-cell"><img src="{{ $user->profile_photo_url }}" alt=""><div><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></div></div></td>
                    <td>{{ $user->role_label }}<small>{{ $user->category_label }}</small></td>
                    <td>{{ $user->document_verifications_count }} dokumen<small>{{ $user->pending_documents_count }} menunggu</small></td>
                    <td><span class="status {{ $user->status }}">{{ $user->status_label }}</span></td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                    <td><a class="btn btn-sm btn-light" href="{{ route('admin.users.show', $user) }}">Periksa</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state">Tidak ada akun yang sesuai filter.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</section>
@endsection
