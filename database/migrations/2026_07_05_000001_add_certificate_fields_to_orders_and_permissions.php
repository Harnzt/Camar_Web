<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'certificate_number')) {
                $table->string('certificate_number')->nullable()->unique()->after('admin_notes');
            }

            if (!Schema::hasColumn('orders', 'certificate_issued_at')) {
                $table->timestamp('certificate_issued_at')->nullable()->after('certificate_number');
            }

            if (!Schema::hasColumn('orders', 'certificate_issued_by')) {
                $table->foreignId('certificate_issued_by')->nullable()->after('certificate_issued_at')
                    ->constrained('users')->nullOnDelete();
            }
        });

        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'certificates.issue'],
            [
                'name' => 'Terbitkan Sertifikat',
                'description' => 'Memverifikasi transaksi dan menerbitkan sertifikat carbon offset',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('roles')->where('slug', 'auditor')->update([
            'name' => 'Auditor Pemerintah',
            'description' => 'Petugas pemerintah untuk verifikasi proyek dan penerbitan sertifikat',
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')->where('slug', 'certificates.issue')->value('id');

        foreach (['auditor', 'super_admin'] as $roleSlug) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');

            if ($roleId && $permissionId) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'certificates.issue')->value('id');

        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        DB::table('roles')->where('slug', 'auditor')->update([
            'name' => 'Auditor',
            'description' => 'Petugas verifikasi dokumen akun dan proyek',
            'updated_at' => now(),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'certificate_issued_by')) {
                $table->dropForeign(['certificate_issued_by']);
                $table->dropColumn('certificate_issued_by');
            }

            if (Schema::hasColumn('orders', 'certificate_issued_at')) {
                $table->dropColumn('certificate_issued_at');
            }

            if (Schema::hasColumn('orders', 'certificate_number')) {
                $table->dropUnique(['certificate_number']);
                $table->dropColumn('certificate_number');
            }
        });
    }
};
