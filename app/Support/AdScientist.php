<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
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
     * Uploads a file to the media library. A template's image header refers
     * to it by the returned id; Ad Scientist does not accept image links.
     *
     * @throws RuntimeException when the upload is refused.
     */
    public static function uploadMedia(string $contents, string $filename, string $mimeType): string
    {
        $response = static::request()
            ->attach('file', $contents, $filename, ['Content-Type' => $mimeType])
            ->post(static::url('media/upload'));

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
