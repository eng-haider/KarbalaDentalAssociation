<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // Unguessable key for the attendee's public invitation page (the QR target).
            $table->string('invitation_token', 64)->nullable()->unique()->after('invitation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropUnique(['invitation_token']);
            $table->dropColumn('invitation_token');
        });
    }
};
