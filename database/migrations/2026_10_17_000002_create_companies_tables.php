<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The companies exhibiting at OSIF, each with its own page and shop.
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->text('description')->nullable();
            // Cart orders are sent to this number on WhatsApp.
            $table->string('whatsapp_phone', 32);
            $table->string('booth', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            // Whole Iraqi dinars; no price means "price on request".
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('offer_price')->nullable();
            $table->string('offer_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'is_active', 'sort_order']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Set for company accounts, which only reach their own company's dashboard.
            $table->foreignId('company_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('company_products');
        Schema::dropIfExists('companies');
    }
};
