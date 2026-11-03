@extends('main_page.admin-panel.layout')

@php($isEditing = $project->exists)
@section('title', $isEditing ? 'Edit Proyek' : 'Tambah Proyek')
@section('page-title', $isEditing ? 'Edit Proyek' : 'Tambah Proyek')

@section('content')
<section class="panel">
    <form method="POST" action="{{ $isEditing ? route('admin.projects.manage.update', $project) : route('admin.projects.store') }}" enctype="multipart/form-data" class="project-form">
        @csrf
        @if($isEditing) @method('PUT') @endif

        <div class="form-grid">
            <label>Nama proyek<input name="name" value="{{ old('name', $project->name) }}" required maxlength="255"></label>
            <label>Nama pengembang / perusahaan<input name="company_name" value="{{ old('company_name', $project->company_name) }}" required maxlength="255"></label>
            <label>Kategori<input name="category" value="{{ old('category', $project->category) }}" required maxlength="100" placeholder="Reforestasi, energi bersih, dll."></label>
            <label>Lokasi<input name="location" value="{{ old('location', $project->location) }}" required maxlength="255"></label>
            <label>Standar sertifikasi<input name="standard" value="{{ old('standard', $project->standard) }}" maxlength="255"></label>
            <label>Durasi (bulan)<input type="number" name="duration_months" value="{{ old('duration_months', $project->duration_months ?? 12) }}" min="1" required></label>
            <label>Harga per ton (Rp)<input type="number" name="price_per_ton" value="{{ old('price_per_ton', $project->price_per_ton) }}" min="0" step="0.01" required></label>
            <label>Stok tersedia (ton)<input type="number" name="stock_available" value="{{ old('stock_available', $project->stock_available ?? 0) }}" min="0" required></label>
            <label>Luas area (ha)<input type="number" name="area_ha" value="{{ old('area_ha', $project->area_ha) }}" min="0"></label>
            <label>Reduksi CO₂ per tahun (ton)<input type="number" name="co2_per_year" value="{{ old('co2_per_year', $project->co2_per_year) }}" min="0"></label>
            <label>Keluarga terdampak<input type="number" name="families_impacted" value="{{ old('families_impacted', $project->families_impacted) }}" min="0"></label>
            <label>Tahun verifikasi<input type="number" name="verified_year" value="{{ old('verified_year', $project->verified_year) }}" min="1900" max="{{ now()->year }}"></label>
            <label class="form-wide">Deskripsi<textarea name="description" rows="5" required>{{ old('description', $project->description) }}</textarea></label>
            <label class="form-wide">Metodologi<textarea name="methodology" rows="5">{{ old('methodology', $project->methodology) }}</textarea></label>
            <label class="form-wide">Gambar proyek<input type="file" name="image" accept="image/jpeg,image/png,image/webp">@if($project->image)<small>Gambar saat ini: {{ $project->image }}</small>@endif</label>
        </div>

        <div class="form-actions">
            <a class="btn btn-light" href="{{ route('admin.projects.index') }}">Batal</a>
            <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Proyek' }}</button>
        </div>
    </form>
</section>
<style>
    .project-form .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .project-form label { display:flex; flex-direction:column; gap:7px; font-weight:700; color:#344054; }
    .project-form input,.project-form textarea { width:100%; padding:11px 12px; border:1px solid #d0d5dd; border-radius:8px; font:inherit; }
    .project-form .form-wide { grid-column:1/-1; }
    .project-form .form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    @media(max-width:700px) { .project-form .form-grid { grid-template-columns:1fr; } .project-form .form-wide { grid-column:auto; } }
</style>
@endsection
