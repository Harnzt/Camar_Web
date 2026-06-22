@extends('main_page.admin-panel.layout')

@section('title', 'Detail Verifikasi')
@section('page-title', 'Detail Verifikasi Pengguna')

@section('content')
<div class="admin-grid detail">
    <section class="panel">
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

        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="review-form">
            @csrf @method('PATCH')
            <label>Status akun</label>
            <select name="status" required>
                @foreach(['pending' => 'Menunggu', 'verified' => 'Verifikasi', 'rejected' => 'Tolak', 'suspended' => 'Nonaktifkan'] as $value => $label)
                    <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <label>Alasan / catatan keputusan</label>
            <textarea name="reason" rows="4" placeholder="Wajib untuk penolakan atau penonaktifan">{{ old('reason', $user->rejection_reason ?? $user->suspension_reason) }}</textarea>
            <button class="btn btn-primary">Simpan Status Akun</button>
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="panel-kicker">Berkas pengguna</span><h2>Verifikasi Dokumen</h2></div></div>
        <div class="document-list">
            @forelse($user->documentVerifications as $document)
                <article class="document-card">
                    <div class="document-head">
                        <div class="list-icon"><i class="fas fa-file-lines"></i></div>
                        <div class="grow"><strong>{{ strtoupper(str_replace('_', ' ', $document->document_type)) }}</strong><span>{{ basename($document->document_path) }}</span></div>
                        <span class="status {{ $document->status }}">{{ str_replace('_', ' ', $document->status) }}</span>
                    </div>
                    <a href="{{ route('admin.documents.download', $document) }}" class="btn btn-light btn-sm"><i class="fas fa-download"></i> Unduh dokumen</a>
                    <form method="POST" action="{{ route('admin.documents.update', $document) }}" class="inline-review">
                        @csrf @method('PATCH')
                        <select name="status">
                            @foreach(['pending' => 'Menunggu', 'approved' => 'Setujui', 'revision_required' => 'Perlu Revisi', 'rejected' => 'Tolak'] as $value => $label)
                                <option value="{{ $value }}" @selected($document->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <textarea name="notes" rows="2" placeholder="Catatan pemeriksaan">{{ $document->notes }}</textarea>
                        <button class="btn btn-primary btn-sm">Simpan</button>
                    </form>
                </article>
            @empty
                <div class="empty-state">Pengguna belum memiliki dokumen.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
