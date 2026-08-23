<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipment_id')->nullable();
            $table->string('seller_order_id')->nullable();
            $table->string('provider')->default('xerin_marketplace');
            $table->string('external_delivery_id')->nullable();
            $table->string('quote_id')->nullable();
            $table->string('status')->default('created');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->string('courier_name')->nullable();
            $table->string('courier_phone')->nullable();
            $table->timestamp('estimated_pickup_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['seller_order_id', 'external_delivery_id']);
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_deliveries');
    }
};
