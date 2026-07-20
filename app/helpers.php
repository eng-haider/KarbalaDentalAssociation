<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a site setting with an optional fallback. */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}
