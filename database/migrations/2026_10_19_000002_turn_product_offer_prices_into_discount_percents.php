<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Companies give a product's discount as a percentage instead of typing the
 * offer price; the offer price is worked out from it. An existing offer price
 * becomes the nearest whole percentage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_products', function (Blueprint $table) {
            $table->unsignedTinyInteger('discount_percent')->nullable()->after('price');
        });

        $offers = DB::table('company_products')
            ->where('price', '>', 0)->whereNotNull('offer_price')->whereColumn('offer_price', '<', 'price')
            ->get(['id', 'price', 'offer_price']);

        foreach ($offers as $offer) {
            $percent = (int) round(100 - $offer->offer_price * 100 / $offer->price);
            DB::table('company_products')->where('id', $offer->id)->update(['discount_percent' => min(99, max(1, $percent))]);
        }

        Schema::table('company_products', function (Blueprint $table) {
            $table->dropColumn('offer_price');
        });
    }

    public function down(): void
    {
        Schema::table('company_products', function (Blueprint $table) {
            $table->unsignedInteger('offer_price')->nullable()->after('price');
        });

        $offers = DB::table('company_products')->where('price', '>', 0)->where('discount_percent', '>', 0)
            ->get(['id', 'price', 'discount_percent']);

        foreach ($offers as $offer) {
            DB::table('company_products')->where('id', $offer->id)
                ->update(['offer_price' => (int) round($offer->price * (100 - $offer->discount_percent) / 100)]);
        }

        Schema::table('company_products', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
