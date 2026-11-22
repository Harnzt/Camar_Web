<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Menerbitkan sertifikat carbon offset secara otomatis setelah pembayaran
 * dikonfirmasi Midtrans. certificate_issued_by bernilai null berarti
 * sertifikat diterbitkan oleh sistem, bukan oleh petugas.
 */
class CertificateService
{
    public const SYSTEM_ISSUER_LABEL = 'Sistem CAMAR (otomatis)';

    public function canIssueFor(Order $order): bool
    {
        return $order->project?->verification_status === 'approved';
    }

    /**
     * Idempoten: order yang sudah punya nomor sertifikat dikembalikan apa adanya.
     */
    public function issueFor(Order $order): Order
    {
        if (filled($order->certificate_number)) {
            return $order;
        }

        $order->forceFill([
            'certificate_number' => $this->generateCertificateNumber($order),
            'certificate_issued_at' => now(),
            'certificate_issued_by' => null,
            'status' => 'completed',
        ])->save();

        return $order;
    }

    private function generateCertificateNumber(Order $order): string
    {
        do {
            $number = 'CAMAR-CERT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('certificate_number', $number)->whereKeyNot($order->id)->exists());

        return $number;
    }
}
