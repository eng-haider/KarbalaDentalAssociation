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
            'card' => OsifInvitation::imageUrl($registration, OsifInvitation::CARD),
            'preview' => OsifInvitation::imageUrl($registration, OsifInvitation::PREVIEW),
        ]);
    }

    /** The dashboard uploads the cards it draws so the page and WhatsApp preview can show them. */
    public function storeImages(Request $request, EventRegistration $registration): JsonResponse
    {
        // Checked here rather than with the auth middleware: the app has no generic login route.
        abort_unless($request->user(), 401);
        abort_unless($request->user()->canManageOsif() && $registration->event?->isOsif(), 403);

        $request->validate([
            'card' => ['required', 'file', 'mimetypes:image/png', 'max:4096'],
            'preview' => ['required', 'file', 'mimetypes:image/jpeg', 'max:2048'],
        ]);

        $directory = dirname($registration->invitationImagePath(OsifInvitation::CARD));
        Storage::disk('public')->putFileAs($directory, $request->file('card'), OsifInvitation::CARD);
        Storage::disk('public')->putFileAs($directory, $request->file('preview'), OsifInvitation::PREVIEW);

        return response()->json([
            'card' => OsifInvitation::imageUrl($registration, OsifInvitation::CARD),
            'preview' => OsifInvitation::imageUrl($registration, OsifInvitation::PREVIEW),
        ]);
    }
}
