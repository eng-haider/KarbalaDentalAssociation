<?php

namespace App\Support;

use App\Models\EventRegistration;
use Illuminate\Support\Facades\Storage;

/**
 * Data for one attendee's invitation. The card is drawn in the browser
 * (resources/js/osif-invitation.js) so Arabic text is shaped correctly without
 * a headless browser on the server; the dashboard then uploads the result so
 * the invitation page and WhatsApp's link preview can show it.
 */
class OsifInvitation
{
    public const CARD = 'card.png';

    public const PREVIEW = 'preview.jpg';

    public const PDF = 'invitation.pdf';

    /** @return array<string, mixed> */
    public static function payload(EventRegistration $registration): array
    {
        $event = $registration->event;
        $number = $registration->whatsappNumber();
        $message = static::message($registration);
        $chat = fn (string $text): ?string => $number ? 'https://wa.me/'.$number.'?text='.rawurlencode($text) : null;

        return [
            'name' => $registration->name,
            'title' => $registration->professional_title,
            'event' => $event->title,
            'brand' => trim('OSIF KARBALA '.$event->starts_at?->year),
            'date' => static::date($registration),
            'time' => static::time($registration),
            'location' => $event->location,
            'code' => $registration->invitationCode(),
            // Encoded in the card's QR code.
            'url' => $registration->invitationUrl(),
            'filename' => 'osif-invitation-'.$registration->getKey().'.png',
            'pdfName' => 'osif-invitation-'.$registration->getKey().'.pdf',
            'message' => $message,
            // Sent before the card image itself, so it carries no link.
            'imageChatUrl' => $chat(static::message($registration, withLink: false)),
            // A link whose preview WhatsApp shows as the card.
            'chatUrl' => $chat($message),
            'uploadUrl' => route('osif.invitation.files', $registration, absolute: false),
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

    /**
     * The WhatsApp message. With the link, WhatsApp shows the card as its
     * preview; without it, the message just introduces the card image.
     */
    public static function message(EventRegistration $registration, bool $withLink = true): string
    {
        $event = $registration->event;

        return implode("\n", array_filter([
            'مرحباً '.$registration->name.'،',
            'يسعدنا تأكيد تسجيلك في '.$event->title.' (OSIF KARBALA).',
            $event->starts_at ? 'الموعد: '.static::date($registration).' – الساعة '.static::time($registration) : null,
            $event->location ? 'المكان: '.$event->location : null,
            '',
            ...($withLink
                ? ['بطاقة الدعوة الخاصة بك ورمز الدخول (QR):', $registration->invitationUrl()]
                : ['بطاقة الدعوة الخاصة بك مرفقة، يرجى إبراز رمز QR عند الحضور.']),
        ], fn (?string $line) => $line !== null));
    }

    /** Absolute URL of an uploaded card file, or null before the dashboard has made one. */
    public static function fileUrl(EventRegistration $registration, string $file): ?string
    {
        $disk = Storage::disk('public');
        $path = $registration->invitationFilePath($file);

        if (! $disk->exists($path)) {
            return null;
        }

        return url($disk->url($path)).'?v='.$disk->lastModified($path);
    }

    public static function date(EventRegistration $registration): ?string
    {
        return $registration->event->starts_at?->translatedFormat('l j F Y');
    }

    public static function time(EventRegistration $registration): ?string
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
