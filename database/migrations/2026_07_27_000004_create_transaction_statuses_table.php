<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->string('color', 20)->default('gray');
            $table->string('icon')->default('bi-circle');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Preserve the two statuses the site already relies on.
        $now = now();

        DB::table('transaction_statuses')->insert([
            [
                'slug' => 'pending',
                'name' => 'قيد الانجاز',
                'color' => 'warning',
                'icon' => 'bi-hourglass-split',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'completed',
                'name' => 'منجزة',
                'color' => 'success',
                'icon' => 'bi-patch-check-fill',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_statuses');
    }
};
