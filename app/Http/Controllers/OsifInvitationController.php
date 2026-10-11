<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\OsifInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OsifInvitationController extends Controller
{
    /** What scanning an invitation's QR code opens. */
    public function show(string $token): Response
    {
        $registration = EventRegistration::query()
            ->where('invitation_token', $token)
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->with('event')
            ->first();

        if (! $registration) {
            return response()->view('osif.invitation-missing', status: 404);
        }

        return response()->view('osif.invitation', [
            'registration' => $registration,
            'event' => $registration->event,
            'card' => OsifInvitation::fileUrl($registration, OsifInvitation::CARD),
            'preview' => OsifInvitation::fileUrl($registration, OsifInvitation::PREVIEW),
            'pdf' => OsifInvitation::fileUrl($registration, OsifInvitation::PDF),
        ]);
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
}
