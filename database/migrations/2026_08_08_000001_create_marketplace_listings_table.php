<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->index();
            $table->string('title');
            $table->text('description');
            $table->string('category', 64)->nullable()->index();
            $table->unsignedBigInteger('price')->nullable();
            $table->string('contact_name');
            $table->string('contact_phone', 32);
            $table->string('city', 64)->nullable();
            $table->string('image')->nullable();
            $table->string('status', 16)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
