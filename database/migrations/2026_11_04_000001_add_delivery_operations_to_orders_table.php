<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_method', 40)->nullable()->after('fulfillment_type');
            $table->string('fulfillment_label', 180)->nullable()->after('fulfillment_method');
            $table->decimal('fulfillment_fee', 14, 2)->default(0)->after('fulfillment_label');
            $table->string('fulfillment_details', 500)->nullable()->after('fulfillment_fee');
            $table->string('delivery_failure_reason', 500)->nullable()->after('delivery_note');
            $table->decimal('refund_requested_amount', 14, 2)->nullable()->after('refund_status');
            $table->string('refund_reason', 80)->nullable()->after('refund_requested_amount');
            $table->timestamp('delivery_attempted_at')->nullable()->after('shipped_at');
            $table->timestamp('rescheduled_for')->nullable()->after('delivery_attempted_at');
            $table->timestamp('returned_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_method',
                'fulfillment_label',
                'fulfillment_fee',
                'fulfillment_details',
                'delivery_failure_reason',
                'refund_requested_amount',
                'refund_reason',
                'delivery_attempted_at',
                'rescheduled_for',
                'returned_at',
            ]);
        });
    }
};
