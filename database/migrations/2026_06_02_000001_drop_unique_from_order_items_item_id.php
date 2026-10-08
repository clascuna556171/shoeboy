<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An item may be re-sold after a reservation is cancelled/released, so
        // the item_id must not be globally unique. Item.status remains the guard.
        // Newer installs already create order_items without the unique index, so
        // only drop it when it is actually present.
        if (Schema::hasIndex('order_items', 'order_items_item_id_unique')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropUnique(['item_id']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('order_items', 'order_items_item_id_unique')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unique('item_id');
            });
        }
    }
};
