<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('seller_profiles')
            ->where('storefront_theme', 'menu')
            ->update([
                'storefront_theme' => 'fashion',
                'storefront_layout' => 'products',
            ]);
    }

    public function down(): void
    {
        DB::table('seller_profiles')
            ->where('storefront_theme', 'fashion')
            ->update([
                'storefront_theme' => 'menu',
                'storefront_layout' => 'services',
            ]);
    }
};
