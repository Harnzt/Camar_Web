<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Business process split agreed on 2026-11-19:
 * - Admin runs the marketplace: buyer account verification, project listings.
 * - Auditor guards carbon integrity: project approval and certificates.
 *
 * Only permissions move. Accounts already verified by an auditor stay verified.
 */
return new class extends Migration
{
    private const ACCOUNT_PERMISSIONS = ['users.verify', 'documents.verify'];

    public function up(): void
    {
        $this->movePermissions(from: 'auditor', to: 'admin');

        $now = now();

        DB::table('roles')->where('slug', 'admin')->update([
            'description' => 'Menjalankan marketplace: verifikasi akun pembeli, mengelola proyek, memantau transaksi, dan mengelola akun auditor.',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'auditor')->update([
            'description' => 'Pemeriksa independen: menyetujui proyek sebelum tayang dan menerbitkan sertifikat.',
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $this->movePermissions(from: 'admin', to: 'auditor');
    }

    private function movePermissions(string $from, string $to): void
    {
        $fromRoleId = DB::table('roles')->where('slug', $from)->value('id');
        $toRoleId = DB::table('roles')->where('slug', $to)->value('id');
        $permissionIds = DB::table('permissions')->whereIn('slug', self::ACCOUNT_PERMISSIONS)->pluck('id');

        if (! $fromRoleId || ! $toRoleId) {
            return;
        }

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $toRoleId,
                'permission_id' => $permissionId,
            ]);
        }

        DB::table('role_permissions')
            ->where('role_id', $fromRoleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
