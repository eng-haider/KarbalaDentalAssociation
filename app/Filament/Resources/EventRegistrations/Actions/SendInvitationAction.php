<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Opens the attendee's invitation card with the steps to send it on WhatsApp.
 * WhatsApp itself is opened through a wa.me link, so no API account is needed.
 * Opening that link records the invitation as sent for the coordinator.
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
            ->modalDescription('افتح واتساب من الزر أدناه؛ تُسجّل الدعوة كمرسلة وتُحدّث حالة المسجّل تلقائياً.')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalContent(fn (EventRegistration $record) => view('filament.osif.invitation-modal', ['record' => $record]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('إغلاق')
            ->action(function (EventRegistration $record): void {
                $record->forceFill([
                    'invitation_sent_at' => now(),
                    'is_handled' => true,
                ])->save();

                Notification::make()
                    ->title('تم إرسال الدعوة وتمت معالجة التسجيل')
                    ->body($record->name)
                    ->success()
                    ->send();
            });
    }
}
