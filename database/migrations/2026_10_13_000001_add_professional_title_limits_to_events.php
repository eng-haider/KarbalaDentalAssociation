<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Seats for each professional title, e.g. {"طبيب أسنان خريج جديد": 50};
        // a title without a number stays open.
        Schema::table('events', function (Blueprint $table) {
            $table->json('professional_title_limits')->nullable()->after('professional_titles');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('professional_title_limits');
        });
    }
};
