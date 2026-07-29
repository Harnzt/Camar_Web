@extends('main_page.admin-panel.layout')

@section('title', 'Detail Verifikasi')
@section('page-title', 'Detail Verifikasi Pengguna')

@section('content')
<div class="admin-grid">
    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Informasi akun</span><h2>Informasi Pengguna</h2></div></div>
        <div class="profile-hero">
            <img src="{{ $user->profile_photo_url }}" alt="">
            <div><span class="status {{ $user->status }}">{{ $user->status_label }}</span><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p></div>
        </div>
        <dl class="detail-list">
            <div><dt>Role</dt><dd>{{ $user->role_label }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $user->category_label }}</dd></div>
            <div><dt>Telepon</dt><dd>{{ $user->phone ?? '-' }}</dd></div>
            <div><dt>Perusahaan</dt><dd>{{ $user->company_name ?? '-' }}</dd></div>
            <div><dt>Industri</dt><dd>{{ $user->industry ?? '-' }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $user->address ?? '-' }}</dd></div>
        </dl>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Berkas pengguna</span><h2>Dokumen Pengguna</h2></div></div>
        <div class="document-list">
            @forelse($user->documentVerifications as $document)
                <article class="document-card">
                    <div class="document-head">
                        <div class="list-icon"><i class="fas fa-file-lines"></i></div>
                        <div class="grow"><strong>{{ strtoupper(str_replace('_', ' ', $document->document_type)) }}</strong><span>{{ basename($document->document_path) }}</span></div>
                        <span class="status {{ $document->status }}">{{ str_replace('_', ' ', $document->status) }}</span>
                    </div>
                    <a href="{{ route('admin.documents.download', $document) }}" class="btn btn-light btn-sm"><i class="fas fa-download"></i> Unduh dokumen</a>
                    @if($document->notes)
                        <p class="muted" style="margin: 10px 0 0;">{{ $document->notes }}</p>
                    @endif
                </article>
            @empty
                <div class="empty-state">Pengguna belum memiliki dokumen.</div>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Keputusan admin</span><h2>Verifikasi Akun</h2></div></div>
        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="review-form">
            @csrf @method('PATCH')
            <label>Status akun</label>
            <select name="status" required>
                @foreach(['pending' => 'Menunggu', 'verified' => 'Verifikasi', 'rejected' => 'Tolak', 'suspended' => 'Nonaktifkan'] as $value => $label)
                    <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <label>Catatan admin</label>
            <textarea name="reason" rows="7" placeholder="Wajib untuk penolakan atau penonaktifan">{{ old('reason', $user->rejection_reason ?? $user->suspension_reason) }}</textarea>
            <button class="btn btn-primary">Simpan Keputusan</button>
        </form>
    </section>
</div>
@endsection
