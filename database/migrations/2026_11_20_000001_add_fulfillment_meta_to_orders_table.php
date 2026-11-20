<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->json('fulfillment_meta')->nullable()->after('fulfillment_details');
            $table->string('collection_code_hash')->nullable()->after('fulfillment_meta');
            $table->timestamp('collection_verified_at')->nullable()->after('collection_code_hash');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['fulfillment_meta', 'collection_code_hash', 'collection_verified_at']);
        });
    }
};
