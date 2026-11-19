@extends('main_page.admin-panel.layout')

@php
    $statusLabels = [
        'pending' => 'Menunggu verifikasi auditor',
        'approved' => 'Disetujui, tayang di marketplace',
        'revision_required' => 'Perlu revisi',
        'rejected' => 'Ditolak',
    ];
    $canReview = Auth::user()->canReviewProjects();
    $canManage = Auth::user()->canManageProjects();
@endphp

@section('title', 'Detail Proyek')
@section('page-title', $canReview ? 'Pemeriksaan Proyek' : 'Detail Proyek')

@section('content')
<a href="{{ route('admin.projects.index') }}" class="back-link"><i aria-hidden="true" class="fas fa-arrow-left"></i> Kembali ke daftar proyek</a>

<div class="admin-grid detail">
    <section class="panel">
        <img class="project-review-image" src="{{ $project->image_url }}" alt="{{ $project->name }}" width="800" height="280">
        <span class="status {{ $project->verification_status }}">{{ $statusLabels[$project->verification_status] ?? $project->verification_status }}</span>
        <h2 class="detail-title">{{ $project->name }}</h2>
        <p class="muted">{{ $project->company_name }} · {{ $project->location }}</p>
        <dl class="detail-list">
            <div><dt>Pengembang / Perusahaan</dt><dd>{{ $project->company_name }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $project->category }}</dd></div>
            <div><dt>Standar</dt><dd>{{ $project->standard ?? '-' }}</dd></div>
            <div><dt>Tahun verifikasi</dt><dd>{{ $project->verified_year ?? '-' }}</dd></div>
            <div><dt>Reduksi CO₂ per tahun</dt><dd>{{ $project->co2_per_year ? number_format($project->co2_per_year).' ton' : '-' }}</dd></div>
            <div><dt>Stok dijual</dt><dd>{{ number_format($project->stock_available) }} ton</dd></div>
            <div><dt>Harga</dt><dd>{{ $project->price_formatted }}</dd></div>
            <div><dt>Durasi</dt><dd>{{ $project->duration_months }} bulan</dd></div>
            <div><dt>Diajukan</dt><dd>{{ $project->submitted_at ? \Illuminate\Support\Carbon::parse($project->submitted_at)->format('d M Y H:i') : '-' }}</dd></div>
        </dl>
        <h3>Deskripsi</h3><p>{{ $project->description ?? '-' }}</p>
        <h3>Metodologi</h3><p>{{ $project->methodology ?? '-' }}</p>
    </section>

    <div class="detail-side sticky-panel">
        <section class="panel">
            <div class="panel-heading"><h2>Dokumen Proyek</h2></div>
            <div class="document-list">
                @forelse($documents as $document)
                    @include('main_page.admin-panel.partials.document-card', [
                        'document' => $document,
                        'title' => $document->display_type,
                        'showStatus' => false,
                    ])
                @empty
                    <div class="empty-state">Belum ada dokumen proyek yang tersimpan.</div>
                @endforelse
            </div>
        </section>

        @if($canReview)
            <section class="panel">
                <div class="panel-heading"><h2>Keputusan Auditor</h2></div>
                <p class="muted panel-note">Proyek hanya tayang di marketplace setelah disetujui.</p>
                <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="review-form">
                    @csrf @method('PATCH')
                    <label for="verification_status">Keputusan</label>
                    <select id="verification_status" name="verification_status" required>
                        @foreach(['pending' => 'Menunggu', 'approved' => 'Setujui', 'revision_required' => 'Minta revisi', 'rejected' => 'Tolak'] as $value => $label)
                            <option value="{{ $value }}" @selected($project->verification_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label for="project-notes">Catatan untuk admin</label>
                    <textarea id="project-notes" name="notes" rows="6" placeholder="Wajib untuk penolakan atau permintaan revisi">{{ old('notes', $project->admin_notes) }}</textarea>
                    <button class="btn btn-primary">Simpan Keputusan</button>
                </form>
            </section>
        @else
            <section class="panel">
                <div class="panel-heading"><h2>Status Verifikasi</h2></div>
                <dl class="detail-list compact">
                    <div><dt>Status</dt><dd><span class="status {{ $project->verification_status }}">{{ $statusLabels[$project->verification_status] ?? $project->verification_status }}</span></dd></div>
                    <div><dt>Diperiksa oleh</dt><dd>{{ $project->reviewer?->name ?? 'Belum diperiksa' }}</dd></div>
                    <div><dt>Waktu pemeriksaan</dt><dd>{{ $project->reviewed_at ? \Illuminate\Support\Carbon::parse($project->reviewed_at)->format('d M Y H:i') : '-' }}</dd></div>
                </dl>
                @if($project->admin_notes)
                    <h3 class="note-heading">Catatan auditor</h3>
                    <p class="auditor-note">{{ $project->admin_notes }}</p>
                @endif

                @if($canManage && in_array($project->verification_status, ['revision_required', 'rejected'], true))
                    <a class="btn btn-primary" href="{{ route('admin.projects.edit', $project) }}">
                        <i aria-hidden="true" class="fas fa-pen"></i> Perbaiki &amp; ajukan ulang
                    </a>
                @elseif($project->verification_status === 'pending')
                    <p class="muted">Proyek sedang menunggu keputusan auditor dan belum tayang di marketplace.</p>
                @endif
            </section>
        @endif
    </div>
</div>
@endsection
