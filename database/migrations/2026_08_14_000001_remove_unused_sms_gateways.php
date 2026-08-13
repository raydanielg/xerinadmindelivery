<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allowedGateways = ['twilio', 'mshastra_sms'];

        DB::table('settings')
            ->where('settings_type', 'sms_config')
            ->whereNotIn('key_name', $allowedGateways)
            ->delete();
    }

    public function down(): void
    {
        // Cannot restore deleted gateways
    }
};
