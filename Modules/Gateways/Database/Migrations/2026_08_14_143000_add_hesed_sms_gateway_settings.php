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

        try {
            $data = [
                'status' => 0,
                'user' => '',
                'pwd' => '',
                'sender_id' => '',
                'otp_template' => 'Your Xerin Express OTP is #OTP#',
            ];

            $existing = DB::table('settings')
                ->where('key_name', 'hesed_sms')
                ->where('settings_type', 'sms_config')
                ->first();

            if (!$existing) {
                DB::table('settings')->insert([
                    'id' => Str::uuid(),
                    'key_name' => 'hesed_sms',
                    'live_values' => json_encode($data),
                    'test_values' => json_encode($data),
                    'settings_type' => 'sms_config',
                    'mode' => 'live',
                    'is_active' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('hesed_sms migration error: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key_name', 'hesed_sms')
            ->where('settings_type', 'sms_config')
            ->delete();
    }
};
