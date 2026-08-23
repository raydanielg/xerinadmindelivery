<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('partner_api_base_url')->nullable()->after('webhook_url');
            $table->string('outbound_webhook_url')->nullable()->after('partner_api_base_url');
            $table->enum('auth_method', ['none', 'api_key'])->default('none')->after('outbound_webhook_url');
            $table->string('api_key_header')->default('X-API-Key')->after('auth_method');
            $table->string('credential_reference')->nullable()->after('api_key_header');
            $table->string('webhook_secret_reference')->nullable()->after('credential_reference');
            $table->text('enabled_events')->nullable()->after('webhook_secret_reference');
            $table->boolean('integration_active')->default(false)->after('enabled_events');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'partner_api_base_url',
                'outbound_webhook_url',
                'auth_method',
                'api_key_header',
                'credential_reference',
                'webhook_secret_reference',
                'enabled_events',
                'integration_active',
            ]);
        });
    }
};
