<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The store links were seeded from placeholders; these are the official
     * listings, so the stored values are replaced rather than only defaulted.
     * The dashboard can still change them afterwards.
     */
    public function up(): void
    {
        $defaults = ManageSettings::defaults();

        foreach (['app_android_url', 'app_ios_url'] as $key) {
            Setting::set($key, $defaults[$key]);
        }
    }

    public function down(): void
    {
        // The previous values were placeholders; nothing worth restoring.
    }
};
