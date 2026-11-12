<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row each time a guest enters a hall: a guest may enter every hall, each once.
        Schema::create('osif_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('hall');
            // Who scanned the card; kept as «unknown» if that account is later deleted.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('checked_in_at');

            $table->unique(['event_registration_id', 'hall']);
            $table->index(['hall', 'user_id']);
        });

        // The hall a «QR scanner» account stands at.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('osif_hall')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('osif_hall');
        });

        Schema::dropIfExists('osif_check_ins');
    }
};
