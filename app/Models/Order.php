<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'order_number',
        'quantity',
        'subtotal',
        'tax',
        'total_price',
        'payment_method',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'status',
        'status_updated_by',
        'status_updated_at',
        'admin_notes',
        'certificate_number',
        'certificate_issued_at',
        'certificate_issued_by',
        'transaction_id', 
        'order_code', 
        'payment_status', 
        'total_amount'
    ];

    protected $casts = [
        'certificate_issued_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusUpdater()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function certificateIssuer()
    {
        return $this->belongsTo(User::class, 'certificate_issued_by');
    }

    public function getTransactionCodeAttribute(): string
    {
        return $this->order_number;
    }

    public function getOffsetTonAttribute(): int
    {
        return (int) $this->quantity;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->total_price, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'verified' => 'Diverifikasi Auditor',
            'completed' => 'Sertifikat Terbit',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
            'failed' => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => '#f59e0b',
            'paid' => '#3b82f6',
            'verified' => '#8b5cf6',
            'completed' => '#10b981',
            'cancelled', 'failed' => '#ef4444',
            'refunded' => '#6b7280',
            default => '#6b7280',
        };
    }
}
