<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\OsifInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OsifInvitationController extends Controller
{
    /** What scanning an invitation's QR code opens. */
    public function show(string $token): Response
    {
        $registration = $this->registrationFor($token);

        if (! $registration) {
            return response()->view('osif.invitation-missing', status: 404);
        }

        $card = OsifInvitation::fileUrl($registration, OsifInvitation::CARD);
        $preview = OsifInvitation::fileUrl($registration, OsifInvitation::PREVIEW);

        return response()->view('osif.invitation', [
            'registration' => $registration,
            'event' => $registration->event,
            'card' => $card,
            'preview' => $preview,
            'ogImage' => $this->ogImage($card, $preview),
            'pdf' => OsifInvitation::fileUrl($registration, OsifInvitation::PDF),
            'cardDownload' => OsifInvitation::downloadUrl($registration, OsifInvitation::CARD),
            'pdfDownload' => OsifInvitation::downloadUrl($registration, OsifInvitation::PDF),
        ]);
    }

    /**
     * WhatsApp does not make a rich preview for a PNG response. This small
     * page keeps the requested /card URL while supplying its Open Graph image.
     */
    public function card(string $token): Response
    {
        $registration = $this->registrationFor($token);

        if (! $registration) {
            return response()->view('osif.invitation-missing', status: 404);
        }

        $card = OsifInvitation::fileUrl($registration, OsifInvitation::CARD);
        abort_unless($card, 404);

        return response()->view('osif.invitation-card', [
            'registration' => $registration,
            'event' => $registration->event,
            'card' => $card,
            'ogImage' => $this->ogImage($card, null),
            'cardDownload' => OsifInvitation::downloadUrl($registration, OsifInvitation::CARD),
        ]);
    }

    /**
     * Stream invitation images through Laravel instead of the public /storage
     * symlink. Some shared hosts deny that nested path even when the upload is
     * present, which prevents the card and WhatsApp preview from loading.
     */
    public function cardImage(string $token): StreamedResponse
    {
        return $this->serveFile($token, OsifInvitation::CARD);
    }

    public function preview(string $token): StreamedResponse
    {
        return $this->serveFile($token, OsifInvitation::PREVIEW);
    }

    public function pdf(string $token): StreamedResponse
    {
        return $this->serveFile($token, OsifInvitation::PDF);
    }

    public function downloadCard(string $token): StreamedResponse
    {
        return $this->serveFile($token, OsifInvitation::CARD, download: true);
    }

    public function downloadPdf(string $token): StreamedResponse
    {
        return $this->serveFile($token, OsifInvitation::PDF, download: true);
    }

    /**
     * The dashboard uploads the card, its PDF and the link preview it draws,
     * with the invitation type (standard or VIP) the card was drawn for.
     */
    public function storeFiles(Request $request, EventRegistration $registration): JsonResponse
    {
        $this->authorizeCoordinator($request, $registration);

        $request->validate([
            'card' => ['required', 'file', 'mimetypes:image/png', 'max:4096'],
            'preview' => ['required', 'file', 'mimetypes:image/jpeg', 'max:2048'],
            'pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:8192'],
            'vip' => ['nullable', 'boolean'],
        ]);

        if ($request->filled('vip')) {
            $registration->forceFill(['is_vip' => $request->boolean('vip')])->save();
        }

        $directory = dirname($registration->invitationFilePath(OsifInvitation::CARD));
        $files = [OsifInvitation::CARD => 'card', OsifInvitation::PREVIEW => 'preview', OsifInvitation::PDF => 'pdf'];

        foreach ($files as $name => $field) {
            if ($request->hasFile($field)) {
                Storage::disk('public')->putFileAs($directory, $request->file($field), $name);
            }
        }

        return response()->json(collect($files)
            ->mapWithKeys(fn (string $field, string $name) => [$field => OsifInvitation::fileUrl($registration, $name)])
            ->all());
    }

    /** What «إرسال للجميع» draws the next card from. */
    public function payload(Request $request, EventRegistration $registration): JsonResponse
    {
        $this->authorizeCoordinator($request, $registration);

        return response()->json(OsifInvitation::payload($registration));
    }

    /**
     * Sends one attendee's uploaded card through the WhatsApp API for
     * «إرسال للجميع». Anyone already invited is skipped with a 409.
     */
    public function send(Request $request, EventRegistration $registration): JsonResponse
    {
        $this->authorizeCoordinator($request, $registration);
        abort_unless(OsifInvitation::canSendByApi($request->user()), 403);

        // Claimed before sending, so two runs at once never invite anyone twice.
        $wasHandled = $registration->is_handled;
        $claimed = EventRegistration::query()
            ->whereKey($registration->getKey())
            ->whereNull('invitation_sent_at')
            ->update(['invitation_sent_at' => now(), 'is_handled' => true]);

        if (! $claimed) {
            return response()->json(['message' => 'أُرسلت الدعوة سابقاً.'], 409);
        }

        try {
            OsifInvitation::send($registration->refresh());
        } catch (Throwable $exception) {
            report($exception);
            $registration->forceFill(['invitation_sent_at' => null, 'is_handled' => $wasHandled])->save();

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['sent' => true]);
    }

    /** Checked here rather than with the auth middleware: the app has no generic login route. */
    private function authorizeCoordinator(Request $request, EventRegistration $registration): void
    {
        abort_unless($request->user(), 401);
        abort_unless($request->user()->canManageOsif() && $registration->event?->isOsif(), 403);
    }

    private function registrationFor(string $token): ?EventRegistration
    {
        return EventRegistration::query()
            ->where('invitation_token', $token)
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->with('event')
            ->first();
    }

    /** @return array{url: string, type: string, width: int, height: int}|null */
    private function ogImage(?string $card, ?string $preview): ?array
    {
        if ($card) {
            return [
                'url' => $card,
                'type' => 'image/png',
                'width' => 1080,
                'height' => 1350,
            ];
        }

        if ($preview) {
            return [
                'url' => $preview,
                'type' => 'image/jpeg',
                'width' => 1200,
                'height' => 630,
            ];
        }

        return null;
    }

    private function serveFile(string $token, string $file, bool $download = false): StreamedResponse
    {
        $registration = $this->registrationFor($token);
        abort_unless($registration, 404);

        $disk = Storage::disk('public');
        $path = $registration->invitationFilePath($file);
        abort_unless($disk->exists($path), 404);

        $headers = [
            // The version appended to the public URL changes whenever a card
            // is regenerated, so browsers and WhatsApp may safely cache it.
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($download) {
            return $disk->download($path, OsifInvitation::downloadFilename($registration, $file), $headers);
        }

        return $disk->response($path, headers: $headers);
    }
}
