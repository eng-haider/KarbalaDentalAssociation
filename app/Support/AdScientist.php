<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Sends approved WhatsApp Business templates through Ad Scientist
 * (ai.adscientist.iq), so a message reaches the attendee from the
 * association's number without anyone opening WhatsApp.
 */
class AdScientist
{
    public static function configured(): bool
    {
        return filled(config('services.adscientist.key'));
    }

    /**
     * A template's definition (status, header, buttons) as Ad Scientist holds
     * it, so a message is built for the template's actual layout. Kept for a
     * few minutes; a template edited in the dashboard is picked up after that.
     *
     * @return array<string, mixed>|null Null when no template has that name and language.
     *
     * @throws RuntimeException when the templates cannot be read.
     */
    public static function template(string $name, string $language): ?array
    {
        $templates = Cache::remember('adscientist.templates', now()->addMinutes(5), function (): array {
            $response = static::request()->get(static::url('templates'));

            if ($response->failed() || $response->json('success') === false) {
                static::fail($response);
            }

            return $response->json('data') ?? [];
        });

        return collect($templates)
            ->first(fn (array $template): bool => $template['name'] === $name && $template['language'] === $language);
    }

    /**
     * Uploads a file to the media library. A template's image header refers
     * to it by the returned id; Ad Scientist does not accept image links.
     *
     * Their upload fails for API keys (it records the uploader as a user and
     * stores "api-key", which is not one), so when a dashboard login is
     * configured the file is uploaded signed in as that user, as their own
     * dashboard does.
     *
     * @throws RuntimeException when the upload is refused.
     */
    public static function uploadMedia(string $contents, string $filename, string $mimeType): string
    {
        $upload = fn (PendingRequest $request): Response => $request
            ->attach('file', $contents, $filename, ['Content-Type' => $mimeType])
            ->post(static::url('media/upload'));

        if (static::canSignIn()) {
            $response = $upload(static::signedIn());

            // An expired session signs in again, once.
            if ($response->status() === 401) {
                Cache::forget('adscientist.session');
                $response = $upload(static::signedIn());
            }
        } else {
            $response = $upload(static::request());
        }

        $id = $response->json('data.id');

        if ($response->failed() || $response->json('success') === false || blank($id)) {
            static::fail($response);
        }

        return $id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $components  The template's header, body and button parameters.
     * @return array<string, mixed> The API's response.
     *
     * @throws RuntimeException when the API refuses the message.
     */
    public static function sendTemplate(string $phoneNumber, string $template, array $components, string $language = 'ar'): array
    {
        $response = static::request()->post(static::url('messages/send-template'), [
            'phoneNumber' => $phoneNumber,
            'templateName' => $template,
            'languageCode' => $language,
            'components' => $components,
        ]);

        if ($response->failed() || $response->json('success') === false) {
            static::fail($response);
        }

        return $response->json() ?? [];
    }

    /**
     * The numbers an invitation did not reach, from an uploaded CSV or Excel
     * file. In a sending report exported from the Ad Scientist dashboard
     * they are the failed rows (the API accepts a message before Meta
     * decides whether to deliver it, so a refused one only shows up there),
     * less any number the report also shows as delivered or read: a later
     * send reached it. A file without a recipient_status column, such as
     * «تصدير الأرقام» makes, is already a list of them.
     *
     * @return list<string> As WhatsApp::number() writes them.
     *
     * @throws RuntimeException when the file has no phone_number column.
     */
    public static function failedNumbers(string $path): array
    {
        $rows = IOFactory::load($path, 0, [IOFactory::READER_CSV, IOFactory::READER_XLSX])
            ->getActiveSheet()
            ->toArray(null, false, false);

        // «phone_number» in their CSV; a copy saved from Excel may read «Phone Number».
        $columns = array_map(fn ($heading): string => str_replace(' ', '_', strtolower(trim((string) $heading))), array_shift($rows) ?? []);
        $phoneColumn = array_search('phone_number', $columns, true);
        $statusColumn = array_search('recipient_status', $columns, true);

        if ($phoneColumn === false) {
            throw new RuntimeException('لا يحتوي الملف على عمود phone_number: ارفع تقرير الإرسال من Ad Scientist، أو ملف أرقام فيه هذا العمود.');
        }

        $failed = $reached = [];

        foreach ($rows as $row) {
            // Excel keeps a long number as a float.
            $phone = $row[$phoneColumn] ?? null;
            $number = WhatsApp::number(is_float($phone) ? number_format($phone, 0, '', '') : (string) $phone);
            $status = $statusColumn === false ? 'failed' : strtolower(trim((string) ($row[$statusColumn] ?? '')));

            if ($number && $status === 'failed') {
                $failed[] = $number;
            } elseif ($number && in_array($status, ['delivered', 'read'], true)) {
                $reached[] = $number;
            }
        }

        return array_values(array_unique(array_diff($failed, $reached)));
    }

    private static function canSignIn(): bool
    {
        return filled(config('services.adscientist.email')) && filled(config('services.adscientist.password'));
    }

    /** Requests as the dashboard user, with a session kept for a few minutes. */
    private static function signedIn(): PendingRequest
    {
        $token = Cache::remember('adscientist.session', now()->addMinutes(10), fn (): string => static::signIn());

        return Http::withToken($token)->acceptJson()->timeout(30);
    }

    private static function signIn(): string
    {
        $response = Http::acceptJson()->timeout(30)->post(static::url('auth/login'), [
            'email' => config('services.adscientist.email'),
            'password' => config('services.adscientist.password'),
        ]);

        if ($response->failed() || $response->json('success') === false) {
            static::fail($response);
        }

        $token = $response->json('data.tokens.accessToken');

        // A verification code or a password change cannot be completed by the website.
        if (blank($token)) {
            throw new RuntimeException('حساب Ad Scientist المستخدم لرفع البطاقات يطلب تحققاً إضافياً (رمز تحقق أو تغيير كلمة المرور)؛ استخدم حساباً بلا تحقق ثنائي.');
        }

        return $token;
    }

    private static function request(): PendingRequest
    {
        return Http::withHeaders(['x-api-key' => config('services.adscientist.key')])
            ->acceptJson()
            ->timeout(30);
    }

    private static function url(string $path): string
    {
        return rtrim(config('services.adscientist.url'), '/').'/'.$path;
    }

    private static function fail(Response $response): never
    {
        $error = $response->json('error.message') ?? $response->json('error') ?? $response->json('message');

        throw new RuntimeException(is_string($error) ? $error : 'WhatsApp API error (HTTP '.$response->status().')');
    }
}
