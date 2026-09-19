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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->enum('method', ['pickup', 'jnt_delivery'])->default('pickup');
            $table->string('tracking_number', 100)->nullable();
            $table->enum('status', ['pending', 'shipped', 'completed'])->default('pending');
            $table->timestamp('date_completed')->nullable();
            $table->timestamps();

            $table->index(['status', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
