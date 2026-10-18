<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product categories become one list, kept by the organisers, that companies
 * pick from instead of typing their own. Whatever companies already typed is
 * carried over into the list.
 */
return new class extends Migration
{
    /** A starting list; organisers rename, reorder or add to it from the dashboard. */
    private const STARTER = [
        'زراعة الأسنان',
        'التطعيم العظمي والأغشية',
        'أدوات جراحية',
        'حشوات ومواد ترميمية',
        'علاج الجذور',
        'التعويضات والتيجان',
        'تقويم الأسنان',
        'أجهزة ومعدات',
        'الأشعة والتصوير',
        'التعقيم والوقاية',
        'مستهلكات عامة',
    ];

    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('company_products', function (Blueprint $table) {
            $table->foreignId('product_category_id')->nullable()->after('name')
                ->constrained('product_categories')->nullOnDelete();
        });

        $order = 0;
        $typed = DB::table('company_products')->whereNotNull('category')->distinct()->pluck('category');

        foreach ([...self::STARTER, ...$typed] as $name) {
            $id = DB::table('product_categories')->where('name', $name)->value('id')
                ?? DB::table('product_categories')->insertGetId([
                    'name' => $name, 'sort_order' => ++$order, 'created_at' => now(), 'updated_at' => now(),
                ]);

            DB::table('company_products')->where('category', $name)->update(['product_category_id' => $id]);
        }

        Schema::table('company_products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('company_products', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('name');
        });

        foreach (DB::table('product_categories')->pluck('name', 'id') as $id => $name) {
            DB::table('company_products')->where('product_category_id', $id)->update(['category' => $name]);
        }

        Schema::table('company_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
        });

        Schema::dropIfExists('product_categories');
    }
};
