<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            // Deactivate all other SMS gateways
            DB::table('settings')
                ->where('settings_type', 'sms_config')
                ->where('key_name', '!=', 'mshastra_sms')
                ->update(['is_active' => 0, 'updated_at' => now()]);

            // Also set status=0 in live_values JSON for other gateways
            $otherGateways = DB::table('settings')
                ->where('settings_type', 'sms_config')
                ->where('key_name', '!=', 'mshastra_sms')
                ->get();

            foreach ($otherGateways as $gw) {
                $values = json_decode($gw->live_values, true);
                if (is_array($values) && isset($values['status'])) {
                    $values['status'] = 0;
                    DB::table('settings')
                        ->where('id', $gw->id)
                        ->update([
                            'live_values' => json_encode($values),
                            'test_values' => json_encode($values),
                            'updated_at' => now(),
                        ]);
                }
            }

            $data = [
                'status' => 1,
                'user' => 'XERINDELIV',
                'pwd' => 'phh4mpe1',
                'sender_id' => 'XERINDELIV',
                'otp_template' => 'Your Zerin Express OTP is #OTP#',
            ];

            $existing = DB::table('settings')
                ->where('key_name', 'mshastra_sms')
                ->where('settings_type', 'sms_config')
                ->first();

            if (!$existing) {
                DB::table('settings')->insert([
                    'id' => Str::uuid(),
                    'key_name' => 'mshastra_sms',
                    'live_values' => json_encode($data),
                    'test_values' => json_encode($data),
                    'settings_type' => 'sms_config',
                    'mode' => 'live',
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')
                    ->where('key_name', 'mshastra_sms')
                    ->where('settings_type', 'sms_config')
                    ->update([
                        'live_values' => json_encode($data),
                        'test_values' => json_encode($data),
                        'is_active' => 1,
                        'updated_at' => now(),
                    ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the migration
            \Log::error('mshastra_sms migration error: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key_name', 'mshastra_sms')
            ->where('settings_type', 'sms_config')
            ->delete();
    }
};
