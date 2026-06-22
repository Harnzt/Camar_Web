<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_code', 'buyer_id', 'project_id', 'emission_calculation_id',
        'quantity', 'price_per_ton', 'total_price', 'offset_ton',
        'status', 'payment_method', 'payment_proof',
        'paid_at', 'certificate_number', 'certificate_issued_at', 'notes', 'transaction_id',
        'customer_name',
        'customer_email',
        'payment_method',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'paid_at'               => 'datetime',
        'certificate_issued_at' => 'datetime',
        'total_price'           => 'decimal:2',
        'offset_ton'            => 'decimal:4',
    ];

    // Auto-generate transaction code
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($trx) {
            if (empty($trx->transaction_code)) {
                $trx->transaction_code = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    // ── Relasi ──────────────────────────────────────────────
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function emissionCalculation()
    {
        return $this->belongsTo(EmissionCalculation::class, 'emission_calculation_id');
    }

    // ── Accessor ─────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Sudah Dibayar',
            'verified'  => 'Terverifikasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'refunded'  => 'Dikembalikan',
            default     => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => '#f59e0b',
            'paid'      => '#3b82f6',
            'verified'  => '#8b5cf6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            'refunded'  => '#6b7280',
            default     => '#6b7280',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

        public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    } 
}