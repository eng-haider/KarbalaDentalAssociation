<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Opens the attendee's invitation card with the steps to send it on WhatsApp.
 * WhatsApp itself is opened through a wa.me link, so no API account is needed;
 * the coordinator confirms with «تم الإرسال» to record the invitation.
 */
class SendInvitationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendInvitation';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (EventRegistration $record): string => $record->invitation_sent_at ? 'إعادة الإرسال' : 'إرسال الدعوة')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color(fn (EventRegistration $record): string => $record->invitation_sent_at ? 'gray' : 'success')
            ->visible(fn (EventRegistration $record): bool => (bool) $record->event?->isOsif())
            ->modalHeading(fn (EventRegistration $record): string => 'دعوة '.$record->name)
            ->modalDescription('أرسل بطاقة الدعوة على واتساب، ثم اضغط «تم الإرسال» لتحديث حالة المسجّل.')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalContent(fn (EventRegistration $record) => view('filament.osif.invitation-modal', ['record' => $record]))
            ->modalSubmitActionLabel('تم الإرسال')
            ->modalSubmitAction(fn (Action $action) => $action->color('success')->icon(Heroicon::OutlinedCheck))
            ->modalCancelActionLabel('إغلاق')
            ->action(function (EventRegistration $record): void {
                $record->forceFill([
                    'invitation_sent_at' => now(),
                    'is_handled' => true,
                ])->save();

                Notification::make()
                    ->title('تم تسجيل إرسال الدعوة إلى '.$record->name)
                    ->success()
                    ->send();
            });
    }
}
