<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('item_id')->unique()->constrained('items')->cascadeOnDelete();
            $table->decimal('awarded_price', 10, 2);
            $table->timestamps();

            $table->index('order_id');
        });

        // Backfill each existing single-item order into the new order_items table.
        DB::table('orders')
            ->whereNotNull('item_id')
            ->orderBy('id')
            ->get()
            ->each(function ($order) {
                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'item_id' => $order->item_id,
                    'awarded_price' => $order->awarded_price,
                    'created_at' => $order->created_at ?? now(),
                    'updated_at' => $order->updated_at ?? now(),
                ]);
            });

        // Drop the now-redundant foreign key column from orders.
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->after('order_number')->constrained('items')->cascadeOnDelete();
        });

        DB::table('order_items')->orderBy('id')->get()->each(function ($row) {
            DB::table('orders')->where('id', $row->order_id)->update(['item_id' => $row->item_id]);
        });

        Schema::dropIfExists('order_items');
    }
};
