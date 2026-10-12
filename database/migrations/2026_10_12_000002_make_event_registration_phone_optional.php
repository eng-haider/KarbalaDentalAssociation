<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registrations added from the dashboard may not have a phone yet;
        // the public forms still require one.
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('phone', 32)->nullable(false)->change();
        });
    }
};
