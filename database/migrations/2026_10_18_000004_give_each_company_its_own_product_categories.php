<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product categories move from one shared list to a list per company, which
 * each company keeps itself. A shared category in use becomes a category of
 * each company using it; the unused shared ones are removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['name']);
        });

        $shared = DB::table('product_categories')->get()->keyBy('id');
        $used = DB::table('company_products')->whereNotNull('product_category_id')
            ->select('company_id', 'product_category_id')->distinct()->get();

        foreach ($used as $pair) {
            $category = $shared[$pair->product_category_id];
            $id = DB::table('product_categories')->insertGetId([
                'company_id' => $pair->company_id,
                'name' => $category->name,
                'sort_order' => $category->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('company_products')
                ->where('company_id', $pair->company_id)
                ->where('product_category_id', $pair->product_category_id)
                ->update(['product_category_id' => $id]);
        }

        DB::table('product_categories')->whereNull('company_id')->delete();

        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable(false)->change();
            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'name']);
        });

        // Back to one shared list: companies' categories with the same name merge into one.
        foreach (DB::table('product_categories')->orderBy('id')->get() as $category) {
            $keep = DB::table('product_categories')->where('name', $category->name)->min('id');

            if ($keep !== $category->id) {
                DB::table('company_products')->where('product_category_id', $category->id)->update(['product_category_id' => $keep]);
                DB::table('product_categories')->where('id', $category->id)->delete();
            }
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->unique('name');
        });
    }
};
