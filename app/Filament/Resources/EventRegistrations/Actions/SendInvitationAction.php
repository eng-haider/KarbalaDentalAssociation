<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use App\Support\OsifInvitation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use RuntimeException;
use Throwable;

/**
 * Opens the attendee's invitation card with the ways to send it on WhatsApp:
 * straight from the association's number through the WhatsApp Business API
 * (for the «مدير النظام (إرسال واتساب)» role only), or by opening a wa.me link by hand. Either way the invitation is recorded
 * as sent for the coordinator.
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
            ->modalDescription(fn (): string => OsifInvitation::canSendByApi(auth()->user())
                ? 'أرسل الدعوة تلقائياً أو افتح واتساب من الزر أدناه؛ تُسجّل الدعوة كمرسلة وتُحدّث حالة المسجّل تلقائياً.'
                : 'افتح واتساب من الزر أدناه؛ تُسجّل الدعوة كمرسلة وتُحدّث حالة المسجّل تلقائياً.')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalContent(fn (EventRegistration $record) => view('filament.osif.invitation-modal', ['record' => $record]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('إغلاق')
            ->action(function (EventRegistration $record, array $arguments, Action $action): void {
                // The dialog's «إرسال الآن» sends the template itself; otherwise wa.me was opened by hand.
                $viaApi = (bool) ($arguments['api'] ?? false);

                if ($viaApi) {
                    try {
                        throw_unless(OsifInvitation::canSendByApi(auth()->user()), RuntimeException::class, 'الإرسال التلقائي متاح لدور «مدير النظام (إرسال واتساب)» فقط.');
                        OsifInvitation::send($record);
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('تعذّر إرسال الدعوة')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }

                $record->forceFill([
                    'invitation_sent_at' => now(),
                    'is_handled' => true,
                ])->save();

                Notification::make()
                    ->title($viaApi ? 'وصلت الدعوة إلى واتساب المسجّل وتمت معالجة التسجيل' : 'تم إرسال الدعوة وتمت معالجة التسجيل')
                    ->body($record->name)
                    ->success()
                    ->send();
            });
    }
}
