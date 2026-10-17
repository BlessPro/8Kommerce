<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('seller_profiles')
            ->where('storefront_theme', 'studio')
            ->update([
                'storefront_theme' => 'fashion',
                'storefront_layout' => 'products',
            ]);
    }

    public function down(): void
    {
        // Intentionally left unchanged: genuine Fashion stores cannot be
        // distinguished safely from stores migrated from Boutique.
    }
};
