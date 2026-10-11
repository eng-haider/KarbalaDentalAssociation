<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('whatsapp_phone', 32)->nullable()->after('phone');
            $table->string('email')->nullable()->after('whatsapp_phone');
            $table->string('professional_title', 120)->nullable()->after('membership_number');
            $table->string('workplace')->nullable()->after('professional_title');
            $table->string('city', 120)->nullable()->after('workplace');
            $table->string('attendance_type', 40)->nullable()->after('city');
            $table->text('notes')->nullable()->after('attendance_type');
            $table->boolean('whatsapp_consent')->default(false)->after('notes');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('membership_number', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_phone',
                'email',
                'professional_title',
                'workplace',
                'city',
                'attendance_type',
                'notes',
                'whatsapp_consent',
            ]);
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('membership_number', 64)->nullable(false)->change();
        });
    }
};
