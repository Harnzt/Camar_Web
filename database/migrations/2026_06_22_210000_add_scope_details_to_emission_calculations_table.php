<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emission_calculations', function (Blueprint $table) {
            $table->string('calculation_mode', 20)->nullable()->after('user_id');
            $table->json('scope_details')->nullable()->after('scope3_kg');
        });
    }

    public function down(): void
    {
        Schema::table('emission_calculations', function (Blueprint $table) {
            $table->dropColumn(['calculation_mode', 'scope_details']);
        });
    }
};
