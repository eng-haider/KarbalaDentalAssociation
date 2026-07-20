<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_icon')->default('bi-patch-check-fill');
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->string('button2_label')->nullable();
            $table->string('button2_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
