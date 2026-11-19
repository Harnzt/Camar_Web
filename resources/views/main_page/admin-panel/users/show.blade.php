@extends('main_page.admin-panel.layout')

@section('title', 'Detail Verifikasi')
@section('page-title', 'Detail Verifikasi Pengguna')

@section('content')
<a href="{{ route('admin.users.index') }}" class="back-link"><i aria-hidden="true" class="fas fa-arrow-left"></i> Kembali ke daftar akun</a>

<div class="admin-grid detail">
    <section class="panel">
        <div class="panel-heading"><h2>Informasi Pengguna</h2></div>
        <div class="profile-hero">
            <img src="{{ $user->profile_photo_url }}" alt="" width="74" height="74">
            <div><span class="status {{ $user->status }}">{{ $user->status_label }}</span><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p></div>
        </div>
        <dl class="detail-list">
            <div><dt>Role</dt><dd>{{ $user->role_label }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $user->category_label }}</dd></div>
            <div><dt>Telepon</dt><dd>{{ $user->phone ?? '-' }}</dd></div>
            @if($user->isPersonal())
                <div><dt>Pekerjaan</dt><dd>{{ $user->job_title ?? '-' }}</dd></div>
            @else
                <div><dt>Perusahaan</dt><dd>{{ $user->company_name ?? '-' }}</dd></div>
                <div><dt>Industri</dt><dd>{{ $user->industry ?? '-' }}</dd></div>
                <div><dt>Jabatan PIC</dt><dd>{{ $user->position ?? '-' }}</dd></div>
            @endif
            <div><dt>Negara</dt><dd>{{ $user->country ?? '-' }}</dd></div>
            <div><dt>Provinsi</dt><dd>{{ $user->province ?? '-' }}</dd></div>
            <div><dt>Kabupaten / Kota</dt><dd>{{ $user->regency ?? '-' }}</dd></div>
            <div><dt>Kecamatan</dt><dd>{{ $user->district ?? '-' }}</dd></div>
            <div><dt>Kode Pos</dt><dd>{{ $user->postal_code ?? '-' }}</dd></div>
            <div><dt>Alamat Lengkap</dt><dd>{{ $user->address ?? '-' }}</dd></div>
        </dl>
    </section>

    <div class="detail-side sticky-panel">
        <section class="panel">
            <div class="panel-heading"><h2>Dokumen Pengguna</h2></div>
            <div class="document-list">
                @forelse($user->documentVerifications as $document)
                    @include('main_page.admin-panel.partials.document-card', [
                        'document' => $document,
                        'title' => strtoupper(str_replace('_', ' ', $document->document_type)),
                    ])
                @empty
                    <div class="empty-state">Pengguna belum memiliki dokumen.</div>
                @endforelse
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading"><h2>Verifikasi Akun</h2></div>
            <form method="POST" action="{{ route('admin.users.status', $user) }}" class="review-form">
                @csrf @method('PATCH')
                <label for="account-status">Status akun</label>
                <select id="account-status" name="status" required>
                    @foreach(['pending' => 'Menunggu', 'verified' => 'Verifikasi', 'rejected' => 'Tolak', 'suspended' => 'Nonaktifkan'] as $value => $label)
                        <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label for="account-reason">Catatan admin</label>
                <textarea id="account-reason" name="reason" rows="7" placeholder="Wajib untuk penolakan atau penonaktifan">{{ old('reason', $user->rejection_reason ?? $user->suspension_reason) }}</textarea>
                <button class="btn btn-primary">Simpan Keputusan</button>
            </form>
        </section>
    </div>
</div>
@endsection
