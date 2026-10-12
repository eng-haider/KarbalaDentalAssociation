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

class OsifInvitationController extends Controller
{
    /** What scanning an invitation's QR code opens. */
    public function show(string $token): Response
    {
        $registration = $this->registrationFor($token);

        if (! $registration) {
            return response()->view('osif.invitation-missing', status: 404);
        }

        return response()->view('osif.invitation', [
            'registration' => $registration,
            'event' => $registration->event,
            'card' => OsifInvitation::fileUrl($registration, OsifInvitation::CARD),
            'preview' => OsifInvitation::fileUrl($registration, OsifInvitation::PREVIEW),
            'pdf' => OsifInvitation::fileUrl($registration, OsifInvitation::PDF),
            'cardDownload' => OsifInvitation::downloadUrl($registration, OsifInvitation::CARD),
            'pdfDownload' => OsifInvitation::downloadUrl($registration, OsifInvitation::PDF),
        ]);
    }

    /**
     * Stream invitation images through Laravel instead of the public /storage
     * symlink. Some shared hosts deny that nested path even when the upload is
     * present, which prevents the card and WhatsApp preview from loading.
     */
    public function card(string $token): StreamedResponse
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

    /** The dashboard uploads the card, its PDF and the link preview it draws. */
    public function storeFiles(Request $request, EventRegistration $registration): JsonResponse
    {
        // Checked here rather than with the auth middleware: the app has no generic login route.
        abort_unless($request->user(), 401);
        abort_unless($request->user()->canManageOsif() && $registration->event?->isOsif(), 403);

        $request->validate([
            'card' => ['required', 'file', 'mimetypes:image/png', 'max:4096'],
            'preview' => ['required', 'file', 'mimetypes:image/jpeg', 'max:2048'],
            'pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:8192'],
        ]);

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

    private function registrationFor(string $token): ?EventRegistration
    {
        return EventRegistration::query()
            ->where('invitation_token', $token)
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->with('event')
            ->first();
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
