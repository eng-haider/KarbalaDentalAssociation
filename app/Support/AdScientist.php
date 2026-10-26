<?php

namespace App\Support;

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
     * @param  array<int, array<string, mixed>>  $components  The template's header, body and button parameters.
     * @return array<string, mixed> The API's response.
     *
     * @throws RuntimeException when the API refuses the message.
     */
    public static function sendTemplate(string $phoneNumber, string $template, array $components, string $language = 'ar'): array
    {
        $response = Http::withHeaders(['x-api-key' => config('services.adscientist.key')])
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim(config('services.adscientist.url'), '/').'/messages/send-template', [
                'phoneNumber' => $phoneNumber,
                'templateName' => $template,
                'languageCode' => $language,
                'components' => $components,
            ]);

        if ($response->failed() || $response->json('success') === false) {
            $error = $response->json('error.message') ?? $response->json('error') ?? $response->json('message');

            throw new RuntimeException(is_string($error) ? $error : 'WhatsApp API error (HTTP '.$response->status().')');
        }

        return $response->json() ?? [];
    }
}
