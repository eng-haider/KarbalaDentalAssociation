<?php

namespace App\Support;

use App\Models\EventRegistration;

/**
 * Data for one attendee's invitation card. The card itself is drawn in the
 * browser (resources/js/osif-invitation.js) so Arabic text is shaped
 * correctly without needing a headless browser on the server.
 */
class OsifInvitation
{
    /** @return array<string, mixed> */
    public static function payload(EventRegistration $registration): array
    {
        $event = $registration->event;
        $number = $registration->whatsappNumber();
        $message = static::message($registration);

        return [
            'name' => $registration->name,
            'title' => $registration->professional_title,
            'event' => $event->title,
            'brand' => trim('OSIF KARBALA '.$event->starts_at?->year),
            'date' => $event->starts_at?->translatedFormat('l j F Y'),
            'time' => static::time($registration),
            'location' => $event->location,
            'code' => $registration->invitationCode(),
            'filename' => 'osif-invitation-'.$registration->getKey().'.png',
            'message' => $message,
            'chatUrl' => $number ? 'https://wa.me/'.$number.'?text='.rawurlencode($message) : null,
            // Same-origin paths keep the canvas exportable even when APP_URL
            // differs from the host the dashboard is opened on.
            'font' => static::path('fonts/cairo/cairo.css'),
            'logos' => [
                'osif' => static::path('images/osif/logos/osif.png'),
                'syndicate' => static::path('images/osif/logos/syndicate.png'),
                'university' => static::path('images/osif/logos/university.png'),
            ],
        ];
    }

    public static function message(EventRegistration $registration): string
    {
        $event = $registration->event;

        return implode("\n", array_filter([
            'مرحباً '.$registration->name.'،',
            'يسعدنا تأكيد تسجيلك في '.$event->title.' (OSIF KARBALA).',
            $event->starts_at ? 'الموعد: '.$event->starts_at->translatedFormat('l j F Y').' – الساعة '.static::time($registration) : null,
            $event->location ? 'المكان: '.$event->location : null,
            'مرفقة بطاقة الدعوة الخاصة بك، يرجى إبرازها عند الحضور.',
        ]));
    }

    private static function time(EventRegistration $registration): ?string
    {
        $startsAt = $registration->event->starts_at;

        if (! $startsAt) {
            return null;
        }

        return $startsAt->format('g:i').' '.($startsAt->format('A') === 'AM' ? 'صباحاً' : 'مساءً');
    }

    private static function path(string $asset): string
    {
        return parse_url(asset($asset), PHP_URL_PATH) ?: '/'.$asset;
    }
}
