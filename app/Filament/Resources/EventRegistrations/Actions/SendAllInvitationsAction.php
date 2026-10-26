<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use App\Support\OsifInvitation;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Sends the «دعوة حضور» on WhatsApp to everyone not invited yet. The dialog
 * does the work in the coordinator's browser, one attendee at a time
 * (resources/js/osif-invitation.js), since only the browser draws the card.
 */
class SendAllInvitationsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendAllInvitations';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('إرسال الدعوات للجميع')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('success')
            // Only the «مدير النظام (إرسال واتساب)» role sends through the API.
            ->visible(fn (): bool => OsifInvitation::canSendByApi(auth()->user()))
            ->modalHeading('إرسال الدعوات للجميع')
            ->modalDescription('تُرسل دعوة الحضور عبر واتساب تلقائياً إلى كل من لم تُرسل له الدعوة بعد، ولا تُعاد لمن أُرسلت له سابقاً.')
            ->modalWidth(Width::TwoExtraLarge)
            ->modalContent(function () {
                $waiting = OsifInvitation::waiting();
                // VIP guests are invited by hand: the template carries the standard wording.
                [$vip, $standard] = $waiting->partition(fn (EventRegistration $registration): bool => $registration->is_vip);
                [$reachable, $noNumber] = $standard->partition(fn (EventRegistration $registration): bool => (bool) $registration->whatsappNumber());

                return view('filament.osif.bulk-invitations-modal', [
                    'queue' => $reachable->map(fn (EventRegistration $registration): array => [
                        'name' => OsifInvitation::guestName($registration),
                        'payload' => route('osif.invitation.payload', $registration, absolute: false),
                    ])->values()->all(),
                    'vip' => $vip->count(),
                    'noNumber' => $noNumber->count(),
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('إغلاق');
    }
}
