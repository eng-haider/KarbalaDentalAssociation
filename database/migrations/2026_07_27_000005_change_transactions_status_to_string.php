<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The status column was an enum limited to pending/completed, which made it
     * impossible to add new statuses from the dashboard. Widening it to a string
     * keeps every existing value while allowing admin-defined statuses.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status', 50)->default('completed')->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed'])->default('completed')->change();
        });
    }
};
