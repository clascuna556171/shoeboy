<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tier 1: below ₱1,000 | Tier 2: ₱1,000–₱1,999.99 | Tier 3: ₱2,000 and up
        DB::table('items')->where('listed_price', '<', 1000)->update(['price_tier' => 'Tier 1']);
        DB::table('items')->where('listed_price', '>=', 1000)->where('listed_price', '<', 2000)->update(['price_tier' => 'Tier 2']);
        DB::table('items')->where('listed_price', '>=', 2000)->update(['price_tier' => 'Tier 3']);
    }

    public function down(): void
    {
        // No safe rollback for a derived value.
    }
};
