<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_accesses', function (Blueprint $table) {
            if (!Schema::hasColumn('module_accesses', 'approve')) {
                $table->boolean('approve')->default(0)->after('export');
            }
            if (!Schema::hasColumn('module_accesses', 'refund')) {
                $table->boolean('refund')->default(0)->after('approve');
            }
            if (!Schema::hasColumn('module_accesses', 'payout')) {
                $table->boolean('payout')->default(0)->after('refund');
            }
        });
    }

    public function down(): void
    {
        Schema::table('module_accesses', function (Blueprint $table) {
            $table->dropColumn(['approve', 'refund', 'payout']);
        });
    }
};
