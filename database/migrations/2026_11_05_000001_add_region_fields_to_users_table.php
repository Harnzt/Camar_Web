<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province', 100)->nullable()->after('country');
            }
            if (! Schema::hasColumn('users', 'regency')) {
                $table->string('regency', 100)->nullable()->after('province');
            }
            if (! Schema::hasColumn('users', 'district')) {
                $table->string('district', 100)->nullable()->after('regency');
            }
            if (! Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(
                ['country', 'province', 'regency', 'district', 'postal_code'],
                fn (string $col) => Schema::hasColumn('users', $col)
            );
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

