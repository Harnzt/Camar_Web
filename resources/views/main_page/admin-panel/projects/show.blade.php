@extends('main_page.admin-panel.layout')

@section('title', 'Detail Proyek')
@section('page-title', 'Pemeriksaan Proyek')

@section('content')
<div class="admin-grid detail">
    <section class="panel">
        <img class="project-review-image" src="{{ $project->image_url }}" alt="{{ $project->name }}">
        <span class="status {{ $project->verification_status }}">{{ str_replace('_', ' ', $project->verification_status) }}</span>
        <h2 class="detail-title">{{ $project->name }}</h2>
        <p class="muted">{{ $project->company_name }} · {{ $project->location }}</p>
        <dl class="detail-list">
            <div><dt>Seller</dt><dd>{{ $project->seller?->name ?? '-' }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $project->category }}</dd></div>
            <div><dt>Standar</dt><dd>{{ $project->standard ?? '-' }}</dd></div>
            <div><dt>Harga</dt><dd>{{ $project->price_formatted }}</dd></div>
            <div><dt>Stok</dt><dd>{{ number_format($project->stock_available) }} ton</dd></div>
            <div><dt>Durasi</dt><dd>{{ $project->duration_months }} bulan</dd></div>
        </dl>
        <h3>Deskripsi</h3><p>{{ $project->description ?? '-' }}</p>
        <h3>Metodologi</h3><p>{{ $project->methodology ?? '-' }}</p>
    </section>

    <section class="panel sticky-panel">
        <div class="panel-heading"><div><span class="panel-kicker">Keputusan admin</span><h2>Verifikasi Proyek</h2></div></div>
        <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="review-form">
            @csrf @method('PATCH')
            <label>Status verifikasi</label>
            <select name="verification_status" required>
                @foreach(['pending' => 'Menunggu', 'approved' => 'Setujui', 'revision_required' => 'Minta Revisi', 'rejected' => 'Tolak'] as $value => $label)
                    <option value="{{ $value }}" @selected($project->verification_status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <label>Catatan admin</label>
            <textarea name="notes" rows="7" placeholder="Wajib untuk penolakan atau permintaan revisi">{{ old('notes', $project->admin_notes) }}</textarea>
            <button class="btn btn-primary">Simpan Keputusan</button>
        </form>
    </section>
</div>
@endsection
