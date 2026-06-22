@extends('main_page.layout.app')

@section('title', 'Edit Proyek Carbon Offset - CAMAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-seller.css') }}">
<style>
    .btn-back { display: inline-flex; align-items: center; gap: 0.5rem; color: #124170; text-decoration: none; font-weight: 700; margin-bottom: 1.5rem; }
    .btn-back:hover { color: #26667F; }
</style>
@endpush

@section('content')
<div class="seller-wrapper" style="padding-top: 2rem; padding-bottom: 4rem;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        
        <a href="{{ route('seller.dashboard') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div class="panel panel-upload">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i class="fas fa-edit"></i>
                    Edit Proyek: {{ $project->name }}
                </h2>
            </div>
            <div class="panel-body">
                
                {{-- 🔥 FORM EDIT: Menembak rute update dengan spoofing method PUT --}}
                <form action="{{ route('seller.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nama Proyek <span class="req">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $project->name) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kategori <span class="req">*</span></label>
                            <select name="category" required>
                                <option value="mangrove" {{ $project->category == 'mangrove' ? 'selected' : '' }}>Mangrove</option>
                                <option value="forest" {{ $project->category == 'forest' ? 'selected' : '' }}>Forest</option>
                                <option value="solar" {{ $project->category == 'solar' ? 'selected' : '' }}>Solar Panel</option>
                                <option value="wind" {{ $project->category == 'wind' ? 'selected' : '' }}>Wind Energy</option>
                                <option value="other" {{ $project->category == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Standar Sertifikasi</label>
                            <select name="standard">
                                <option value="" {{ $project->standard == '' ? 'selected' : '' }}>Pilih standar</option>
                                <option value="Verra VCS" {{ $project->standard == 'Verra VCS' ? 'selected' : '' }}>Verra VCS</option>
                                <option value="Gold Standard" {{ $project->standard == 'Gold Standard' ? 'selected' : '' }}>Gold Standard</option>
                                <option value="Plan Vivo" {{ $project->standard == 'Plan Vivo' ? 'selected' : '' }}>Plan Vivo</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Lokasi <span class="req">*</span></label>
                            <input type="text" name="location" value="{{ old('location', $project->location) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Harga per Ton (Rp) <span class="req">*</span></label>
                            <input type="number" name="price_per_ton" value="{{ old('price_per_ton', $project->price_per_ton) }}" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Stok Tersedia (ton) <span class="req">*</span></label>
                            <input type="number" name="stock_available" value="{{ old('stock_available', $project->stock_available) }}" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>CO₂ per Tahun (ton)</label>
                            <input type="number" name="co2_per_year" value="{{ old('co2_per_year', $project->co2_per_year) }}" min="0" step="0.1">
                        </div>
                        
                        <div class="form-group">
                            <label>Luas Area (ha)</label>
                            <input type="number" name="area_ha" value="{{ old('area_ha', $project->area_ha) }}" min="0" step="0.1">
                        </div>
                        
                        <div class="form-group">
                            <label>Keluarga Terdampak</label>
                            <input type="number" name="families_impacted" value="{{ old('families_impacted', $project->families_impacted) }}" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Durasi (bulan)</label>
                            <input type="number" name="duration_months" value="{{ old('duration_months', $project->duration_months) }}" min="1">
                        </div>
                        
                        <div class="form-group full">
                            <label>Deskripsi Proyek <span class="req">*</span></label>
                            <textarea name="description" rows="4" required>{{ old('description', $project->description) }}</textarea>
                        </div>
                        
                        <div class="form-group full">
                            <label>Metodologi</label>
                            <textarea name="methodology" rows="2">{{ old('methodology', $project->methodology) }}</textarea>
                        </div>
                        
                        <div class="form-group full">
                            <label>Foto Proyek (Biarkan kosong jika tidak ingin diganti)</label>
                            <div class="file-drop" onclick="document.getElementById('projectImage').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Klik untuk memilih foto baru jika ingin mengganti</span>
                                @if($project->image)
                                    <small style="color: #10b981; display: block; font-weight: 700; margin-top: 5px;">Gambar saat ini: {{ $project->image }}</small>
                                @endif
                            </div>
                            <input type="file" id="projectImage" name="image" accept="image/*" style="display:none;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit-project" style="background: #124170; margin-top: 1.5rem; cursor: pointer;">
                        <i class="fas fa-save"></i> Simpan Perubahan Proyek
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection