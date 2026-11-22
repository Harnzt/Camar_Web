<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sertifikat kini diterbitkan otomatis oleh sistem setelah webhook Midtrans
 * mengonfirmasi pembayaran, sehingga permission penerbitan manual dihapus.
 */
return new class extends Migration
{
    private const PERMISSION = 'certificates.issue';

    private const ROLES_WITH_PERMISSION = ['auditor', 'super_admin'];

    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('slug', self::PERMISSION)->value('id');

        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        DB::table('roles')->where('slug', 'auditor')->update([
            'description' => 'Pemeriksa independen: menyetujui proyek sebelum tayang dan memantau transaksi serta audit log.',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => self::PERMISSION],
            [
                'name' => 'Terbitkan Sertifikat',
                'description' => 'Memverifikasi transaksi dan menerbitkan sertifikat carbon offset',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $permissionId = DB::table('permissions')->where('slug', self::PERMISSION)->value('id');

        foreach (self::ROLES_WITH_PERMISSION as $roleSlug) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');

            if ($roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        DB::table('roles')->where('slug', 'auditor')->update([
            'description' => 'Pemeriksa independen: menyetujui proyek sebelum tayang dan menerbitkan sertifikat.',
            'updated_at' => $now,
        ]);
    }
};
