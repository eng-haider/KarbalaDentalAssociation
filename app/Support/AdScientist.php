<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
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
        return Cache::remember("adscientist.template.{$name}.{$language}", now()->addMinutes(5), function () use ($name, $language): ?array {
            $response = static::request()->get(static::url('templates'));

            if ($response->failed() || $response->json('success') === false) {
                static::fail($response);
            }

            return collect($response->json('data'))
                ->first(fn (array $template): bool => $template['name'] === $name && $template['language'] === $language);
        });
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
