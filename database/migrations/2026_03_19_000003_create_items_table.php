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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('brand', 100)->default('Li-Ning');
            $table->string('model', 150)->default('Sneaker');
            $table->string('price_tier', 50)->default('Tier 1');
            $table->decimal('listed_price', 10, 2);
            $table->string('condition', 50)->default('Good');
            $table->string('size', 20)->default('US 9.0');
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');
            $table->string('triage_status', 50)->default('available'); // washing, under_repair, available
            $table->decimal('repair_cost', 10, 2)->default(0.00);
            $table->string('category', 50)->default('Basketball');
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
