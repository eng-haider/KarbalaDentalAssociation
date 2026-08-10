<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

/**
 * Registers the home page sections in their default order.
 *
 * Safe to re-run on a live site: existing rows are left untouched, so an order
 * the admin has already arranged is never reset. Only sections added to the
 * code since the last run are inserted, and they land at the end.
 */
class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        // New sections go after whatever is already ordered, never on top of it.
        $next = HomeSection::count() ? (int) HomeSection::max('sort_order') + 1 : 0;

        foreach (HomeSection::defaults() as $key => $name) {
            $existing = HomeSection::where('key', $key)->first();

            if ($existing) {
                continue;
            }

            HomeSection::create([
                'key' => $key,
                'name' => $name,
                'sort_order' => $next++,
                'is_visible' => true,
            ]);
        }
    }
}
