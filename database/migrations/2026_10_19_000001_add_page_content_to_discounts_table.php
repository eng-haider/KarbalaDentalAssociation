<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Each offer's own page: its full text, and a PDF or Word file to open or download.
            $table->longText('body')->nullable()->after('description');
            $table->string('attachment')->nullable()->after('logo');
            $table->string('attachment_name')->nullable()->after('attachment');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['body', 'attachment', 'attachment_name']);
        });
    }
};
