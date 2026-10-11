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
            // Choices for the "الصفة المهنية" select, managed from the dashboard.
            $table->json('professional_titles')->nullable()->after('registration_open');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->boolean('owns_clinic')->nullable()->after('professional_title');
            $table->timestamp('invitation_sent_at')->nullable()->after('is_handled');
        });

        DB::table('events')
            ->where('slug', 'osif-karbala-2026')
            ->whereNull('professional_titles')
            ->update(['professional_titles' => json_encode([
                'طبيب أسنان اختصاص',
                'طبيب أسنان مقيم أقدم',
                'طبيب أسنان تدرج',
                'طبيب أسنان مقيم',
                'طبيب أسنان خريج جديد',
            ], JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['owns_clinic', 'invitation_sent_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('professional_titles');
        });
    }
};
