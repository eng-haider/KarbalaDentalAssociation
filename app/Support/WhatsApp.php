<?php

namespace App\Support;

class WhatsApp
{
    /**
     * A phone number in the international form wa.me expects
     * (07801234567 → 9647801234567).
     */
    public static function number(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Iraqi numbers are usually typed locally (07…), sometimes as +964 07….
        if (str_starts_with($digits, '9640')) {
            return '964'.substr($digits, 4);
        }

        if (str_starts_with($digits, '0')) {
            return '964'.substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '7')) {
            return '964'.$digits;
        }

        return $digits;
    }

    /** The number as Iraqis write it (9647801234567 → 07801234567). */
    public static function local(?string $phone): ?string
    {
        $number = static::number($phone);

        return $number && str_starts_with($number, '964') && strlen($number) === 13
            ? '0'.substr($number, 3)
            : $number;
    }

    /** A chat with the number, optionally with a message typed in. */
    public static function chatUrl(?string $phone, ?string $text = null): ?string
    {
        $number = static::number($phone);

        if (! $number) {
            return null;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }
}
