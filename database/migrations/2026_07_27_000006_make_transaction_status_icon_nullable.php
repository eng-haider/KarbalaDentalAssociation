<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clearing the icon field in the dashboard submits null, which the NOT NULL
     * column rejected. The icon is optional — the site falls back to bi-circle.
     */
    public function up(): void
    {
        Schema::table('transaction_statuses', function (Blueprint $table) {
            $table->string('icon')->nullable()->default('bi-circle')->change();
        });
    }

    public function down(): void
    {
        DB::table('transaction_statuses')->whereNull('icon')->update(['icon' => 'bi-circle']);

        Schema::table('transaction_statuses', function (Blueprint $table) {
            $table->string('icon')->nullable(false)->default('bi-circle')->change();
        });
    }
};
