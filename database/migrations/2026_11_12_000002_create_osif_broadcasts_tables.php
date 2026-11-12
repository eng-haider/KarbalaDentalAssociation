<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One WhatsApp template sent to everyone registered for OSIF (an announcement or reminder).
        Schema::create('osif_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('template', 100);
            $table->string('image_path')->nullable();
            // The image as Ad Scientist's media library holds it: uploaded once, used for every message.
            $table->string('media_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // One row per number, so a stopped run carries on without sending anyone the message twice.
        Schema::create('osif_broadcast_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osif_broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->string('name');
            // Taken by a sender at this moment: a second tab never sends the same message too.
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('error', 500)->nullable();

            $table->unique(['osif_broadcast_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osif_broadcast_messages');
        Schema::dropIfExists('osif_broadcasts');
    }
};
