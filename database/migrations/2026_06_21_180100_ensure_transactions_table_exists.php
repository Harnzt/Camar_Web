<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('emission_calculation_id')->nullable()
                ->constrained('emission_calculations')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('price_per_ton', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('offset_ton', 10, 4);
            $table->enum('status', [
                'pending', 'paid', 'verified', 'completed', 'cancelled', 'refunded',
            ])->default('pending');
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
        // Tabel ini mungkin berasal dari migration lama; jangan hapus secara otomatis.
    }
};
