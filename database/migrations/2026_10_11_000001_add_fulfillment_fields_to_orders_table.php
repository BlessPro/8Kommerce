<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_type', 24)->nullable()->after('delivery_required');
            $table->string('refund_status', 24)->nullable()->index()->after('payment_status');
            $table->timestamp('seller_response_due_at')->nullable()->index()->after('paid_at');
            $table->timestamp('accepted_at')->nullable()->after('seller_response_due_at');
            $table->timestamp('processing_at')->nullable()->after('accepted_at');
            $table->timestamp('ready_at')->nullable()->after('processing_at');
            $table->timestamp('shipped_at')->nullable()->after('ready_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['refund_status']);
            $table->dropIndex(['seller_response_due_at']);
            $table->dropColumn([
                'fulfillment_type',
                'refund_status',
                'seller_response_due_at',
                'accepted_at',
                'processing_at',
                'ready_at',
                'shipped_at',
                'delivered_at',
                'cancelled_at',
            ]);
        });
    }
};
