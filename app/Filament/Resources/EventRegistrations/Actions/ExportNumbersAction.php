<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads the WhatsApp numbers of the registrations the table shows (its
 * tab, filters and search) as an Excel file: one number per row, each once,
 * in the international form WhatsApp tools expect (9647801234567).
 */
class ExportNumbersAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'exportNumbers';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('تصدير الأرقام')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->tooltip('ملف Excel بأرقام واتساب للمسجّلين الظاهرين في الجدول، حسب التبويب والفلاتر والبحث.')
            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
            ->action(function (ListRecords $livewire): StreamedResponse {
                $numbers = $livewire->getFilteredSortedTableQuery()
                    ->get()
                    ->map(fn (EventRegistration $registration): ?string => $registration->whatsappNumber())
                    ->filter()
                    ->unique()
                    ->values();

                $spreadsheet = new Spreadsheet;
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'phone_number');

                foreach ($numbers as $index => $number) {
                    // As text, so Excel shows the whole number rather than 9.6478E+12.
                    $sheet->setCellValueExplicit('A'.($index + 2), $number, DataType::TYPE_STRING);
                }

                $sheet->getColumnDimension('A')->setAutoSize(true);

                return response()->streamDownload(
                    fn () => (new Xlsx($spreadsheet))->save('php://output'),
                    'phone-numbers-'.now()->format('Y-m-d').'.xlsx',
                    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                );
            });
    }
}
