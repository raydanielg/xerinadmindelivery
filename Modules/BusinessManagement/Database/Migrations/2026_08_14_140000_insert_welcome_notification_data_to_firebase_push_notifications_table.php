<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('firebase_push_notifications')) {
            $existing = DB::table('firebase_push_notifications')->where('name', 'welcome_customer')->first();
            if (!$existing) {
                DB::table('firebase_push_notifications')->insert([
                    'name' => 'welcome_customer',
                    'value' => 'Welcome to {businessName}! Your account has been created successfully. Enjoy your rides with us.',
                    'status' => 1,
                    'type' => 'others',
                    'group' => 'customer',
                    'action' => 'welcome_customer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('firebase_push_notifications')->where('name', 'welcome_customer')->delete();
    }
};
