<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Registrations in all; reaching it closes the form. Null: no overall limit.
            $table->unsignedInteger('registration_limit')->nullable()->after('registration_open');
        });

        // OSIF KARBALA 2026 takes 600.
        DB::table('events')->where('slug', 'osif-karbala-2026')->update(['registration_limit' => 600]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('registration_limit');
        });
    }
};
