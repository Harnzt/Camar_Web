<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmissionCalculation extends Model
{
    protected $fillable = [
        'user_id',
        'scope1_kg', 'scope2_kg', 'scope3_kg',
        'total_kg', 'total_ton',
        'fuel_consumption', 'fuel_factor',
        'electricity_consumption', 'electricity_factor',
        'transport_distance', 'transport_factor',
        'waste_amount', 'waste_factor',
        'estimated_cost', 'price_per_ton',
        'is_offset',
    ];

    protected $casts = [
        'is_offset' => 'boolean',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'emission_calculation_id');
    }

    // Berapa kg yang sudah di-offset dari kalkulasi ini
    public function getOffsetKgAttribute(): float
    {
        return (float) $this->transactions()
            ->whereIn('status', ['verified', 'completed'])
            ->sum('offset_ton') * 1000;
    }

    // Persentase offset (0-100)
    public function getOffsetPercentageAttribute(): float
    {
        if ($this->total_kg <= 0) return 0;
        return min(100, round(($this->offset_kg / $this->total_kg) * 100, 1));
    }
}