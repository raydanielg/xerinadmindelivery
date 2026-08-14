<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $allowedGateways = ['twilio', 'mshastra_sms', 'hesed_sms'];

        DB::table('settings')
            ->where('settings_type', 'sms_config')
            ->whereNotIn('key_name', $allowedGateways)
            ->delete();

        // Ensure mshastra_sms exists
        $mshastra = DB::table('settings')
            ->where('key_name', 'mshastra_sms')
            ->where('settings_type', 'sms_config')
            ->first();

        if (!$mshastra) {
            DB::table('settings')->insert([
                'id' => Str::uuid(),
                'key_name' => 'mshastra_sms',
                'live_values' => json_encode([
                    'status' => 0,
                    'user' => '',
                    'pwd' => '',
                    'sender_id' => '',
                    'otp_template' => 'Your Xerin Express OTP is #OTP#',
                ]),
                'test_values' => json_encode([
                    'status' => 0,
                    'user' => '',
                    'pwd' => '',
                    'sender_id' => '',
                    'otp_template' => 'Your Xerin Express OTP is #OTP#',
                ]),
                'settings_type' => 'sms_config',
                'mode' => 'live',
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure hesed_sms exists
        $hesed = DB::table('settings')
            ->where('key_name', 'hesed_sms')
            ->where('settings_type', 'sms_config')
            ->first();

        if (!$hesed) {
            DB::table('settings')->insert([
                'id' => Str::uuid(),
                'key_name' => 'hesed_sms',
                'live_values' => json_encode([
                    'status' => 0,
                    'user' => '',
                    'pwd' => '',
                    'sender_id' => '',
                    'otp_template' => 'Your Xerin Express OTP is #OTP#',
                ]),
                'test_values' => json_encode([
                    'status' => 0,
                    'user' => '',
                    'pwd' => '',
                    'sender_id' => '',
                    'otp_template' => 'Your Xerin Express OTP is #OTP#',
                ]),
                'settings_type' => 'sms_config',
                'mode' => 'live',
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Cannot restore deleted gateways
    }
};
