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
            // The type can be switched in the send dialog, so both messages travel with the card data.
            'vip' => (bool) $registration->is_vip,
            'chatUrls' => [
                'standard' => $chat(static::message($registration, vip: false)),
                'vip' => $chat(static::message($registration, vip: true)),
            ],
            'name' => static::guestName($registration),
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
            // WhatsApp fetches the Open Graph image from this page and shows
            // the invitation card as a rich preview.
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
     * The WhatsApp message. With the link, the guest opens the invitation
     * page and WhatsApp shows its card preview. VIP guests are invited
     * rather than told their registration is confirmed.
     */
    public static function message(EventRegistration $registration, bool $withLink = true, ?bool $vip = null): string
    {
        $event = $registration->event;
        $vip ??= (bool) $registration->is_vip;

        return implode("\n", array_filter([
            'مرحباً '.static::guestName($registration).'،',
            $vip
                ? 'يسعدنا توجيه دعوة خاصة (VIP) لحضوركم '.$event->title.' (OSIF KARBALA).'
                : 'يسعدنا تأكيد تسجيلك في '.$event->title.' (OSIF KARBALA).',
            $event->starts_at ? 'الموعد: '.static::date($registration).' – الساعة '.static::time($registration) : null,
            $event->location ? 'المكان: '.$event->location : null,
            'تشمل الدعوة وجبة غداء.',
            'يرجى إبراز رمز QR لاستلام وجبة الغداء.',
            '',
            ...($withLink
                ? [$vip ? 'بطاقة دعوتكم الخاصة (VIP) ورمز الدخول (QR):' : 'بطاقة الدعوة الخاصة بك ورمز الدخول (QR):', $registration->invitationShareUrl()]
                : ['بطاقة الدعوة الخاصة بك مرفقة، يرجى إبراز الدعوة للدخول من البوابة الرئيسية، ورمز QR لاستلام وجبة الغداء.']),
        ], fn (?string $line) => $line !== null));
    }

    /**
     * The name as the invitation addresses the guest: «د.» first, unless they
     * already typed a title themselves (د. / د / دكتور / الدكتور / Dr).
     */
    public static function guestName(EventRegistration $registration): string
    {
        $name = trim($registration->name);

        if (preg_match('/^(?:د\s*[.\/]|د\s|(?:ال)?دكتور|dr\b)/iu', $name)) {
            return $name;
        }

        return 'د. '.$name;
    }

    /**
     * Absolute URL of an uploaded invitation file, or null before the
     * dashboard has made one. Files are streamed by Laravel rather than by
     * /storage so they keep working on hosts that block storage symlinks.
     */
    public static function fileUrl(EventRegistration $registration, string $file): ?string
    {
        $disk = Storage::disk('public');
        $path = $registration->invitationFilePath($file);

        if (! $disk->exists($path)) {
            return null;
        }

        return route(static::routeName($file), $registration->invitationToken())
            .'?v='.$disk->lastModified($path);
    }

    /** A forced download is clearer and more reliable than a browser download attribute on mobile. */
    public static function downloadUrl(EventRegistration $registration, string $file): ?string
    {
        $path = $registration->invitationFilePath($file);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return match ($file) {
            static::CARD => route('osif.invitation.card.download', $registration->invitationToken()),
            static::PDF => route('osif.invitation.pdf.download', $registration->invitationToken()),
            default => throw new \InvalidArgumentException("Invitation file [{$file}] cannot be downloaded."),
        };
    }

    public static function downloadFilename(EventRegistration $registration, string $file): string
    {
        return match ($file) {
            static::CARD => 'osif-invitation-'.$registration->getKey().'.png',
            static::PDF => 'osif-invitation-'.$registration->getKey().'.pdf',
            default => throw new \InvalidArgumentException("Invitation file [{$file}] cannot be downloaded."),
        };
    }

    /** What the card, page and PDF call this invitation. */
    public static function label(EventRegistration $registration): string
    {
        return $registration->is_vip ? 'دعوة VIP' : 'دعوة حضور';
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

    private static function routeName(string $file): string
    {
        return match ($file) {
            static::CARD => 'osif.invitation.card.image',
            static::PREVIEW => 'osif.invitation.preview',
            static::PDF => 'osif.invitation.pdf',
            default => throw new \InvalidArgumentException("Unknown invitation file [{$file}]."),
        };
    }
}
