<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getSchemaBuilder()->hasTable('sms_logs')) {
            DB::table('sms_logs')
                ->where('type', 'otp')
                ->update(['message' => '[OTP REDACTED]']);
        }
    }

    public function down(): void
    {
    }
};
