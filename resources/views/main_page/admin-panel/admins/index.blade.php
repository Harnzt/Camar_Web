@extends('main_page.admin-panel.layout')

@section('title', 'Kelola Admin')
@section('page-title', 'Manajemen Administrator')

@section('content')
<div class="admin-grid detail">
    <section class="panel sticky-panel">
        <div class="panel-heading"><div><span class="panel-kicker">Akun baru</span><h2>Tambah Administrator</h2></div></div>
        <form method="POST" action="{{ route('admin.admins.store') }}" class="review-form">
            @csrf
            <label>Nama</label><input name="name" required value="{{ old('name') }}">
            <label>Email</label><input type="email" name="email" required value="{{ old('email') }}">
            <label>Role</label>
            <select name="role"><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select>
            <label>Password awal</label><input type="password" name="password" required minlength="8">
            <button class="btn btn-primary">Buat Administrator</button>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Kontrol akses</span><h2>Daftar Administrator</h2></div></div>
        <div class="admin-account-list">
            @foreach($admins as $admin)
                <form method="POST" action="{{ route('admin.admins.update', $admin) }}" class="admin-account-card">
                    @csrf @method('PATCH')
                    <div class="document-head">
                        <img class="admin-avatar" src="{{ $admin->profile_photo_url }}" alt="">
                        <div class="grow"><strong>{{ $admin->name }}</strong><span>{{ $admin->email }}</span></div>
                        <span class="status {{ $admin->status }}">{{ $admin->role_label }}</span>
                    </div>
                    <div class="form-grid">
                        <input name="name" value="{{ $admin->name }}" required>
                        <input type="email" name="email" value="{{ $admin->email }}" required>
                        <select name="role"><option value="admin" @selected($admin->role === 'admin')>Admin</option><option value="super_admin" @selected($admin->role === 'super_admin')>Super Admin</option></select>
                        <select name="status"><option value="verified" @selected($admin->status === 'verified')>Aktif</option><option value="suspended" @selected($admin->status === 'suspended')>Nonaktif</option></select>
                    </div>
                    <input type="password" name="password" placeholder="Password baru (opsional)">
                    <button class="btn btn-light btn-sm">Simpan Perubahan</button>
                </form>
            @endforeach
        </div>
        {{ $admins->links() }}
    </section>
</div>
@endsection
