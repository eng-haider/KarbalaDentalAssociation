<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Support\AdScientist;
use App\Support\OsifInvitation;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * Puts the invitations WhatsApp did not deliver back to «بانتظار الدعوة», so
 * «إرسال الدعوات للجميع» sends them again. The API accepts an invitation
 * before Meta decides (a spam limit, a number not on WhatsApp), so it was
 * recorded as sent; the sending report exported from the Ad Scientist
 * dashboard is what lists the ones that failed.
 */
class ReturnUndeliveredInvitationsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'returnUndeliveredInvitations';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('إرجاع الدعوات التي لم تصل')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('warning')
            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
            ->modalHeading('إرجاع الدعوات التي لم تصل')
            ->modalDescription('ارفع تقرير الإرسال من لوحة Ad Scientist (ملف CSV أو Excel). كل دعوة فشل وصولها تعود إلى «بانتظار الدعوة» لتُرسل من جديد.')
            ->schema([
                FileUpload::make('report')
                    ->label('تقرير الإرسال')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    // Read once here; the report is not kept.
                    ->storeFiles(false)
                    ->required(),
            ])
            ->modalSubmitActionLabel('إرجاع الدعوات')
            ->action(function (array $data, Action $action): void {
                try {
                    $numbers = AdScientist::failedNumbers($data['report']->getRealPath());
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('تعذّرت قراءة التقرير')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $returned = OsifInvitation::returnUndelivered($numbers);

                Notification::make()
                    ->title($returned
                        ? "عادت {$returned} دعوة إلى «بانتظار الدعوة»"
                        : 'لا توجد دعوات مُرسلة لأرقام هذا التقرير')
                    ->body('في التقرير '.count($numbers).' رقماً لم تصله الدعوة.')
                    ->status($returned ? 'success' : 'warning')
                    ->send();
            });
    }
}
