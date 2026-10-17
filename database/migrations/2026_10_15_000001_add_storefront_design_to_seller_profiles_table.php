<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('storefront_theme', 24)->default('classic')->after('public_slug');
            $table->string('storefront_layout', 24)->default('products')->after('storefront_theme');
        });
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn(['storefront_theme', 'storefront_layout']);
        });
    }
};
