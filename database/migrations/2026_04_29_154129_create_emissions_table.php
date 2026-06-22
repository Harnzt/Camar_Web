<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── TABEL PROJECTS ──────────────────────────────────────
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name');
            $table->string('category');
            $table->string('location')->nullable();
            $table->string('standard')->nullable();             // e.g. VCS + CCB Gold
            $table->integer('duration_months')->default(12);
            $table->decimal('price_per_ton', 15, 2);
            $table->integer('stock_available')->default(0);
            $table->bigInteger('area_ha')->nullable();
            $table->bigInteger('co2_per_year')->nullable();
            $table->integer('families_impacted')->nullable();
            $table->integer('verified_year')->nullable();
            $table->text('description')->nullable();
            $table->text('methodology')->nullable();
            $table->string('image')->nullable();                // path gambar utama
            $table->timestamps();
        });

        // ── TABEL EMISSION CALCULATIONS ─────────────────────────
        // Menyimpan hasil kalkulasi emisi dari kalkulator
        Schema::create('emission_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Hasil per scope (dalam kg CO₂)
            $table->decimal('scope1_kg', 15, 4)->default(0);
            $table->decimal('scope2_kg', 15, 4)->default(0);
            $table->decimal('scope3_kg', 15, 4)->default(0);
            $table->decimal('total_kg', 15, 4)->default(0);
            $table->decimal('total_ton', 15, 6)->default(0);

            // Input yang digunakan
            $table->decimal('fuel_consumption', 15, 4)->nullable();
            $table->decimal('fuel_factor', 10, 4)->nullable();
            $table->decimal('electricity_consumption', 15, 4)->nullable();
            $table->decimal('electricity_factor', 10, 4)->nullable();
            $table->decimal('transport_distance', 15, 4)->nullable();
            $table->decimal('transport_factor', 10, 4)->nullable();
            $table->decimal('waste_amount', 15, 4)->nullable();
            $table->decimal('waste_factor', 10, 4)->nullable();

            // Estimasi biaya offset saat kalkulasi dilakukan
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('price_per_ton', 10, 2)->default(50000);

            // Apakah kalkulasi ini sudah di-offset?
            $table->boolean('is_offset')->default(false);

            $table->timestamps();
        });

        // ── TABEL TRANSACTIONS ──────────────────────────────────
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('emission_calculation_id')->nullable()
                ->constrained('emission_calculations')->nullOnDelete();
            $table->integer('quantity');           // jumlah ton yang dibeli
            $table->decimal('price_per_ton', 15, 2);  // harga per ton saat beli
            $table->decimal('total_price', 15, 2);
            $table->decimal('offset_ton', 10, 4);
            $table->enum('status', ['pending','paid','verified','completed','cancelled','refunded'])
                ->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('certificate_number')->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        }); 
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('emission_calculations');
        Schema::dropIfExists('projects');
    }
};