<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        try {
            $apiKey = 'AIzaSyAcEVgv9R639z4B8VdxQNeBIfkg2ME7Opw';

            // Set Firebase OTP verification status
            $existingStatus = DB::table('business_settings')
                ->where('key_name', 'firebase_otp_verification_status')
                ->where('settings_type', 'firebase_otp')
                ->first();

            if (!$existingStatus) {
                DB::table('business_settings')->insert([
                    'key_name' => 'firebase_otp_verification_status',
                    'settings_type' => 'firebase_otp',
                    'value' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('business_settings')
                    ->where('key_name', 'firebase_otp_verification_status')
                    ->where('settings_type', 'firebase_otp')
                    ->update(['value' => 0, 'updated_at' => now()]);
            }

            // Set Firebase OTP Web API Key
            $existingKey = DB::table('business_settings')
                ->where('key_name', 'firebase_otp_web_api_key')
                ->where('settings_type', 'firebase_otp')
                ->first();

            if (!$existingKey) {
                DB::table('business_settings')->insert([
                    'key_name' => 'firebase_otp_web_api_key',
                    'settings_type' => 'firebase_otp',
                    'value' => $apiKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('business_settings')
                    ->where('key_name', 'firebase_otp_web_api_key')
                    ->where('settings_type', 'firebase_otp')
                    ->update(['value' => $apiKey, 'updated_at' => now()]);
            }
        } catch (\Exception $e) {
            \Log::error('Firebase OTP migration error: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        DB::table('business_settings')
            ->where('settings_type', 'firebase_otp')
            ->delete();
    }
};
