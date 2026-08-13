<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway')->nullable();
                $table->string('receiver')->nullable();
                $table->text('message')->nullable();
                $table->string('type')->default('otp')->comment('otp, message, notification');
                $table->string('status')->default('pending')->comment('success, error, pending');
                $table->text('response')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('gateway');
                $table->index('receiver');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
