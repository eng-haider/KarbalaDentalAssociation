<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('title');
            $table->string('tag')->nullable();
            $table->text('description')->nullable();
            $table->string('value_label')->default('خصم خاص');
            $table->string('value_caption')->nullable();
            $table->json('perks')->nullable();
            $table->string('link')->nullable();
            $table->string('icon')->default('bi-tag');
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
