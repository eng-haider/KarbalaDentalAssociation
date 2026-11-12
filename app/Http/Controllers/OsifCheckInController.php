<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\OsifCheckIn;
use App\Models\User;
use App\Support\OsifInvitation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The conference-day scanner. Each hall has its own scanner at the door: a
 * «QR scanner» account fixed to its hall, or OSIF staff who choose the hall
 * on the page. A guest enters each hall once; a second scan at the same hall
 * says when it was. Every scanner sees how many cards they have read.
 */
class OsifCheckInController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        // The OSIF dashboard's login (email and password) brings staff back here.
        if (! $request->user()) {
            return redirect()->guest(route('filament.osif.auth.login'));
        }

        abort_unless($request->user()->canCheckInOsifGuests(), 403);

        $hall = $this->hall($request);

        // A scanner account without a hall has to be given one in «المستخدمون والصلاحيات».
        abort_if($hall === null && $request->user()->isOsifScanner(), 403, 'لم تُحدَّد قاعة لهذا الحساب.');

        return response()->view('osif.checkin', [
            'hall' => $hall,
            'halls' => OsifCheckIn::HALLS,
            'canChangeHall' => ! $request->user()->isOsifScanner(),
            'stats' => $hall ? $this->stats($request->user(), $hall) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);
        abort_unless($request->user()->canCheckInOsifGuests(), 403);

        $data = $request->validate(['code' => ['required', 'string', 'max:500']]);
        $hall = $this->hall($request);

        if ($hall === null) {
            return response()->json(['status' => 'error', 'message' => 'اختر القاعة أولاً.'], 422);
        }

        $registration = $this->find($data['code']);

        if (! $registration) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'هذه البطاقة غير موجودة في تسجيلات الملتقى.',
                'stats' => $this->stats($request->user(), $hall),
            ], 404);
        }

        // Claimed in one statement (the hall's unique key), so two scanners never both admit the same card.
        $now = now();
        $checkedIn = OsifCheckIn::query()->insertOrIgnore([
            'event_registration_id' => $registration->getKey(),
            'hall' => $hall,
            'user_id' => $request->user()->getKey(),
            'checked_in_at' => $now,
        ]) > 0;

        if ($checkedIn) {
            // The guest's first hall is when they entered the conference.
            EventRegistration::query()
                ->whereKey($registration->getKey())
                ->whereNull('checked_in_at')
                ->update(['checked_in_at' => $now]);
        }

        $time = $checkedIn
            ? EventRegistration::karbalaTime($now)
            : $registration->checkIns()->where('hall', $hall)->first()?->time();

        return response()->json([
            'status' => $checkedIn ? 'checked_in' : 'already',
            'name' => OsifInvitation::guestName($registration),
            'title' => $registration->is_vip ? null : $registration->cardTitle(),
            'vip' => (bool) $registration->is_vip,
            'code' => $registration->invitationCode(),
            'time' => $time,
            'stats' => $this->stats($request->user(), $hall),
        ]);
    }

    /** A scanner's own hall; staff send theirs with the page (?hall=1) and each scan. */
    private function hall(Request $request): ?int
    {
        $hall = $request->user()->isOsifScanner()
            ? $request->user()->checkInHall()
            : (int) $request->input('hall');

        return isset(OsifCheckIn::HALLS[$hall]) ? $hall : null;
    }

    /**
     * What a scan or the typed box gives: the invitation link from the QR
     * code, the code printed on the card (OSIF-0012, or just 12), or the last
     * characters of the link's token: a QR reader set to type only those
     * (10 or so instead of the whole 94-character link) is many times faster.
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

        // The token's end: accepted only when it belongs to exactly one guest.
        if (preg_match('~^[A-Za-z0-9]{8,19}$~', $code)) {
            $matches = $query->where('invitation_token', 'like', '%'.$code)->limit(2)->get();

            return $matches->count() === 1 ? $matches->first() : null;
        }

        return null;
    }

    /** @return array{mine: int, hall: int, total: int} */
    private function stats(User $user, int $hall): array
    {
        // Both hall counts in one query: every scan asks for them.
        $counts = OsifCheckIn::query()->toBase()
            ->where('hall', $hall)
            ->selectRaw('count(*) as hall_total, sum(case when user_id = ? then 1 else 0 end) as mine', [$user->getKey()])
            ->first();

        return [
            'mine' => (int) $counts->mine,
            'hall' => (int) $counts->hall_total,
            'total' => $this->osifRegistrations()->count(),
        ];
    }

    /** @return Builder<EventRegistration> */
    private function osifRegistrations(): Builder
    {
        return EventRegistration::query()
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG));
    }
}
