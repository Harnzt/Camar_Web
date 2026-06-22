<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    // 1. Definisikan nama tabel (HANYA SEKALI)
    protected $table = 'projects';

    // 2. Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'seller_id',
        'verification_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'admin_notes',
        'submitted_at',
        'name',
        'company_name',
        'category',
        'location',
        'standard',
        'duration_months',
        'price_per_ton',
        'stock_available',
        'area_ha',
        'co2_per_year',
        'families_impacted',
        'verified_year',
        'description',
        'methodology',
        'image',
    ];

    // 3. Konversi tipe data otomatis
    protected $casts = [
        'price_per_ton'     => 'float',
        'stock_available'   => 'integer',
        'area_ha'           => 'integer',
        'co2_per_year'      => 'integer',
        'families_impacted' => 'integer',
        'duration_months'   => 'integer',
        'verified_year'     => 'integer',
        'reviewed_at'       => 'datetime',
        'submitted_at'      => 'datetime',
    ];
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'project_id', 'id');
    }

    /**
     * URL gambar — fallback ke placeholder jika kosong
     */

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        // Jika kolom 'image' di DB berisi "proyek1.jpg"
        // Maka akan mencari file di: public/images/proyek1.jpg
        if ($this->image && file_exists(public_path('images/' . $this->image))) {
            return asset('images/' . $this->image);
        }

        // Gambar cadangan jika file tidak ditemukan
        return asset('images/placeholder-project.jpg');
    }

    /**
     * Harga dalam format Rupiah
     */
    public function getPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->price_per_ton, 0, ',', '.');
    }

    /**
     * CO2 per tahun dalam ribuan (contoh: 125K)
     */
    public function getCo2KAttribute(): string
    {
        if (!$this->co2_per_year) return '-';
        return number_format($this->co2_per_year / 1000, 0) . 'K';
    }

    public function scopeActive($query)
    {
        return $query; // Sesuaikan jika kamu menambah kolom status nanti
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
