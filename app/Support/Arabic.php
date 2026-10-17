<?php

namespace App\Support;

class Arabic
{
    /**
     * A count with the noun in its correct Arabic form: one, two, 3–10, 11+.
     * Arabic::count(6, ['منتج واحد', 'منتجان', 'منتجات', 'منتجاً']) → «6 منتجات».
     *
     * @param  array{0: string, 1: string, 2: string, 3: string}  $forms
     */
    public static function count(int $count, array $forms): string
    {
        return match (true) {
            $count === 1 => $forms[0],
            $count === 2 => $forms[1],
            $count >= 3 && $count <= 10 => $count.' '.$forms[2],
            default => $count.' '.$forms[3],
        };
    }

    public static function products(int $count): string
    {
        return static::count($count, ['منتج واحد', 'منتجان', 'منتجات', 'منتجاً']);
    }

    public static function offers(int $count): string
    {
        return static::count($count, ['عرض واحد', 'عرضان', 'عروض', 'عرضاً']);
    }
}
