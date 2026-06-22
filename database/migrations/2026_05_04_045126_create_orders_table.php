<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->string('order_number')->unique();
        $table->integer('quantity');
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('tax', 15, 2)->default(0);
        $table->decimal('total_price', 15, 2);
        $table->string('payment_method')->nullable();
        $table->string('buyer_name')->nullable();
        $table->string('buyer_email')->nullable();
        $table->string('buyer_phone')->nullable();
        $table->string('status')->default('pending');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
