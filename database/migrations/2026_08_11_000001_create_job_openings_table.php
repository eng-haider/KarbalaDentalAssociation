<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // `jobs` is taken by the queue table, so the vacancies live in `job_openings`.
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('employer');
            $table->string('type', 16)->default('full_time')->index();
            $table->string('specialty')->nullable();
            $table->string('city', 64)->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('salary')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 32)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('apply_link')->nullable();
            $table->string('logo')->nullable();
            $table->date('closes_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
