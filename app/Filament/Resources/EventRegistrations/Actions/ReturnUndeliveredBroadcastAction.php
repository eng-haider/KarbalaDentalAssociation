<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\OsifBroadcast;
use App\Support\AdScientist;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * As «إرجاع الدعوات التي لم تصل», for a message sent to everyone («msg»,
 * «whait_lap»): the numbers the sending report shows as failed go back to
 * waiting in that template's latest run, so «متابعة الإرسال» in its dialog
 * sends them again, and only them.
 */
class ReturnUndeliveredBroadcastAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'returnUndeliveredBroadcast';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('إرجاع الرسائل التي لم تصل')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
            ->modalHeading('إعادة إرسال الرسائل التي لم تصل')
            ->modalDescription('ارفع تقرير الإرسال من لوحة Ad Scientist، فتعود الأرقام الفاشلة فيه فقط إلى الانتظار. بعدها افتح زر إرسال هذه الرسالة للجميع واضغط «متابعة الإرسال»، فتصلهم وحدهم بنفس الصورة.')
            ->schema([
                Select::make('template')
                    ->label('الرسالة')
                    ->options(fn (): array => collect(OsifBroadcast::templateNames())->mapWithKeys(fn (string $name): array => [$name => "قالب {$name}"])->all())
                    // The message sent last, usually the one whose report this is.
                    ->default(fn (): ?string => OsifBroadcast::query()->latest('id')->value('template') ?? OsifBroadcast::labTemplateName())
                    ->selectablePlaceholder(false)
                    ->required(),
                FileUpload::make('report')
                    ->label('تقرير الإرسال أو ملف الأرقام (CSV أو Excel)')
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
            ->modalSubmitActionLabel('إرجاع الرسائل')
            ->action(function (array $data, Action $action): void {
                try {
                    $numbers = AdScientist::failedNumbers($data['report']->getRealPath(), $data['template']);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('تعذّرت قراءة الملف')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                $returned = OsifBroadcast::returnUndelivered($data['template'], $numbers);

                Notification::make()
                    ->title($returned
                        ? "عادت {$returned} رسالة ({$data['template']}) إلى الانتظار"
                        : "لا توجد رسائل {$data['template']} مُرسلة لأرقام هذا الملف")
                    ->body($returned
                        ? 'افتح «إرسال رسالة '.$data['template'].' للجميع» واضغط «متابعة الإرسال».'
                        : 'في الملف '.count($numbers).' رقماً لم تصله الرسالة.')
                    ->status($returned ? 'success' : 'warning')
                    ->persistent()
                    ->send();
            });
    }
}
