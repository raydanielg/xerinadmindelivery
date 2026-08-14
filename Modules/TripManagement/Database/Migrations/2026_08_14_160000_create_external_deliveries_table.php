<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shipment_id')->nullable();
            $table->uuid('seller_order_id');
            $table->string('provider')->default('xerin_marketplace');
            $table->string('external_delivery_id')->nullable();
            $table->string('status')->default('created');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->decimal('delivery_fee', 12, 2)->nullable();
            $table->string('currency')->default('TZS');
            $table->string('courier_name')->nullable();
            $table->string('courier_phone')->nullable();
            $table->timestamp('estimated_pickup_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('quote_id')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shipment_id')->references('id')->on('trip_requests')->nullOnDelete();
            $table->index(['seller_order_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_deliveries');
    }
};
