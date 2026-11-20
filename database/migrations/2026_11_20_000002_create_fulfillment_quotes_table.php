<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fulfillment_quotes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('order_id')->nullable()->index();
            $table->string('public_token', 80)->unique();
            $table->string('status', 30)->default('REQUESTED')->index();
            $table->string('customer_name', 120)->nullable();
            $table->string('customer_phone', 30);
            $table->string('location', 160);
            $table->string('landmark', 160)->nullable();
            $table->string('gps_location', 255)->nullable();
            $table->string('popular_stop', 160)->nullable();
            $table->string('fulfillment_method', 40);
            $table->string('fulfillment_label', 160);
            $table->text('fulfillment_details')->nullable();
            $table->json('items');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('delivery_fee', 14, 2)->nullable();
            $table->decimal('total', 14, 2)->nullable();
            $table->string('currency', 3)->default('GHS');
            $table->text('customer_note')->nullable();
            $table->text('seller_note')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillment_quotes');
    }
};
