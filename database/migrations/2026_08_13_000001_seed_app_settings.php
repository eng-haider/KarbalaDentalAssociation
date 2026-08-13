<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** The keys this migration plants; also what `down()` removes. */
    private const KEYS = ['app_android_url', 'app_ios_url', 'app_intro'];

    /**
     * The store links used to be hardcoded in the renewal section. They now
     * live in the settings so the dashboard can change them — and so clearing
     * one actually hides its button — which only works if the rows exist.
     */
    public function up(): void
    {
        $defaults = ManageSettings::defaults();

        foreach (self::KEYS as $key) {
            if (Setting::query()->whereKey($key)->doesntExist()) {
                Setting::set($key, $defaults[$key]);
            }
        }
    }

    public function down(): void
    {
        Setting::query()->whereKey(self::KEYS)->delete();
    }
};
