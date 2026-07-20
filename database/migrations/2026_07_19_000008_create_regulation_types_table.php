<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon')->default('bi-file-earmark-text');
            $table->text('note')->nullable();
            $table->text('preamble')->nullable();
            $table->json('conditions');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_types');
    }
};
