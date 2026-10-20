<?php

namespace App\Services;

use App\Models\Project;

class ProjectPurchaseDisclosureService
{
    public const TAX_RATE = 0.11;

    /**
     * @return array<string, mixed>
     */
    public function forProject(Project $project, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $pricePerTon = max(0, (float) $project->price_per_ton);
        $subtotal = $quantity * $pricePerTon;
        $tax = round($subtotal * self::TAX_RATE);

        return [
            'currency' => 'IDR',
            'quantity_ton' => $quantity,
            'price_per_ton' => round($pricePerTon, 2),
            'subtotal' => round($subtotal, 2),
            'tax_rate_percentage' => self::TAX_RATE * 100,
            'tax' => $tax,
            'other_fees' => 0,
            'estimated_total' => round($subtotal + $tax, 2),
            'stock_available' => max(0, (int) $project->stock_available),
            'is_estimate' => true,
            'cost_note' => 'CAMAR menghitung harga kredit ditambah PPN 11%. Kode checkout saat ini tidak menambahkan biaya platform lain.',
            'risks' => [
                'Harga dan stok dapat berubah sebelum pesanan dibuat; nilai final mengikuti halaman checkout.',
                'Kredit hanya mengurangi saldo emisi setelah transaksi berstatus dibayar, diverifikasi, atau selesai.',
                'Nama standar pada data proyek belum menggantikan pemeriksaan dokumen atau tautan registri proyek.',
            ],
            'terms' => [
                'Pembeli harus login sebagai buyer dan memiliki hasil perhitungan emisi.',
                'Pembelian menggunakan ton utuh, minimal 1 ton, dan tidak boleh melebihi stok tersedia.',
                'Pembayaran diproses melalui Midtrans; pesanan pending belum dihitung sebagai offset berhasil.',
                'Ketentuan pembatalan dan pengembalian dana belum tersedia pada data yang dapat dibaca Cami; periksa halaman transaksi atau hubungi pengelola sebelum membayar.',
            ],
        ];
    }
}
