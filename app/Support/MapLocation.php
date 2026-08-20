<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Turns the "رابط الخريطة" setting into something a browser can actually render.
 *
 * Google share links (maps.app.goo.gl / goo.gl/maps) are short redirects that
 * refuse to be framed, so pasting one into an <iframe> shows nothing. We follow
 * the redirect once, pull the coordinates out of the expanded URL, cache them,
 * and feed those to Google's key-less embed endpoint.
 */
class MapLocation
{
    /** How long a successful / failed lookup stays cached, in seconds. */
    private const TTL_OK = 60 * 60 * 24 * 30;

    private const TTL_FAIL = 60 * 60;

    /** The raw link saved in the dashboard, if any. */
    public static function link(): ?string
    {
        return setting('map_url');
    }

    public static function address(): string
    {
        return setting('address', 'كربلاء المقدسة – حي الحسين');
    }

    /**
     * ['lat' => float, 'lng' => float] for the configured link, or null.
     * Cached per-link, so editing the setting invalidates it automatically.
     */
    public static function coordinates(): ?array
    {
        $link = static::link();

        if (blank($link)) {
            return null;
        }

        $key = 'map.coords.'.md5($link);
        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached ?: null;
        }

        $coords = static::parse($link) ?? static::parse(static::expand($link));

        Cache::put($key, $coords ?? false, $coords ? static::TTL_OK : static::TTL_FAIL);

        return $coords;
    }

    /** Frameable map URL: exact pin when the link resolved, address search otherwise. */
    public static function embedUrl(): string
    {
        $coords = static::coordinates();

        $query = $coords
            ? $coords['lat'].','.$coords['lng']
            : static::address();

        return 'https://maps.google.com/maps?'.http_build_query([
            'q' => $query,
            'z' => $coords ? 17 : 14,
            'hl' => 'ar',
            'output' => 'embed',
        ]);
    }

    /** Where the "open in maps" button goes. */
    public static function linkUrl(): string
    {
        return static::link()
            ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode(static::address());
    }

    public static function directionsUrl(): string
    {
        $coords = static::coordinates();

        return 'https://www.google.com/maps/dir/?api=1&destination='.urlencode(
            $coords ? $coords['lat'].','.$coords['lng'] : static::address()
        );
    }

    /** Follow the short link (a few hops at most) to the expanded Google Maps URL. */
    private static function expand(string $url): ?string
    {
        try {
            for ($hop = 0; $hop < 5; $hop++) {
                $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; KarbalaDentalAssociation/1.0)'])
                    ->withOptions(['allow_redirects' => false])
                    ->timeout(6)
                    ->get($url);

                $location = $response->header('Location');

                if (blank($location)) {
                    // Last hop: coordinates sometimes only appear in the page body.
                    return $response->body();
                }

                $url = $location;

                if (static::parse($url)) {
                    return $url;
                }
            }

            return $url;
        } catch (Throwable) {
            return null;
        }
    }

    /** Pull the first plausible lat/lng pair out of a Google or OSM URL. */
    private static function parse(?string $subject): ?array
    {
        if (blank($subject)) {
            return null;
        }

        $subject = urldecode($subject);

        $patterns = [
            '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',        // Google place marker
            '/@(-?\d+\.\d+),(-?\d+\.\d+)/',            // Google viewport centre
            '/[?&](?:q|ll|query|destination)=(-?\d+\.\d+),(-?\d+\.\d+)/', // plain q=lat,lng
            '/#map=\d+\/(-?\d+\.\d+)\/(-?\d+\.\d+)/',  // OpenStreetMap
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject, $m)) {
                $lat = (float) $m[1];
                $lng = (float) $m[2];

                if (abs($lat) <= 90 && abs($lng) <= 180) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        return null;
    }
}
