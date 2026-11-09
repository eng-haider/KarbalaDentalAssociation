<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\OsifInvitation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The conference-day gate: staff sign in with their OSIF dashboard account,
 * scan each guest's invitation QR code, and the registration is marked as
 * checked in. A guest is checked in once; a second scan says when it was.
 */
class OsifCheckInController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        // The OSIF dashboard's login (email and password) brings staff back here.
        if (! $request->user()) {
            return redirect()->guest(route('filament.osif.auth.login'));
        }

        abort_unless($request->user()->canManageOsif(), 403);

        return response()->view('osif.checkin', ['stats' => $this->stats()]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);
        abort_unless($request->user()->canManageOsif(), 403);

        $data = $request->validate(['code' => ['required', 'string', 'max:500']]);
        $registration = $this->find($data['code']);

        if (! $registration) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'هذه البطاقة غير موجودة في تسجيلات الملتقى.',
                'stats' => $this->stats(),
            ], 404);
        }

        // Claimed in one statement, so two gates scanning the same card never both admit it.
        $checkedIn = EventRegistration::query()
            ->whereKey($registration->getKey())
            ->whereNull('checked_in_at')
            ->update(['checked_in_at' => now()]);

        $registration->refresh();

        return response()->json([
            'status' => $checkedIn ? 'checked_in' : 'already',
            'name' => OsifInvitation::guestName($registration),
            'title' => $registration->is_vip ? null : $registration->cardTitle(),
            'vip' => (bool) $registration->is_vip,
            'code' => $registration->invitationCode(),
            'time' => $registration->checkInTime(),
            'stats' => $this->stats(),
        ]);
    }

    /**
     * What a scan or the typed box gives: the invitation link from the QR
     * code, or the code printed on the card (OSIF-0012, or just 12).
     */
    private function find(string $code): ?EventRegistration
    {
        // Arabic keyboards type ٠١٢ rather than 012.
        $code = trim(str_replace(
            [...mb_str_split('٠١٢٣٤٥٦٧٨٩'), ...mb_str_split('۰۱۲۳۴۵۶۷۸۹')],
            [...range(0, 9), ...range(0, 9)],
            $code,
        ));

        $query = $this->osifRegistrations();

        if (preg_match('~/osif/invitation/([A-Za-z0-9]{20,64})~', $code, $match)
            || preg_match('~^([A-Za-z0-9]{20,64})$~', $code, $match)) {
            return $query->where('invitation_token', $match[1])->first();
        }

        if (preg_match('~^(?:OSIF\s*-?\s*)?0*(\d{1,9})$~i', $code, $match)) {
            return $query->whereKey((int) $match[1])->first();
        }

        return null;
    }

    /** @return array{checkedIn: int, total: int} */
    private function stats(): array
    {
        $registrations = $this->osifRegistrations();

        return [
            'checkedIn' => (clone $registrations)->whereNotNull('checked_in_at')->count(),
            'total' => $registrations->count(),
        ];
    }

    /** @return Builder<EventRegistration> */
    private function osifRegistrations(): Builder
    {
        return EventRegistration::query()
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->with('event');
    }
}
