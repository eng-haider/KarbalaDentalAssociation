<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads the registrations the table shows (its tab, filters and search)
 * as an Excel file: one registrant per row with everything they entered and
 * where their invitation stands. On the «الكل» tab with no filters, that is
 * everyone registered.
 */
class ExportRegistrationsAction extends Action
{
    private const ATTENDANCE_TYPES = [
        'attendee' => 'حضور الملتقى',
        'workshop' => 'مهتم بالورش',
        'exhibition' => 'زيارة المعرض',
    ];

    public static function getDefaultName(): ?string
    {
        return 'exportRegistrations';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('تصدير المسجّلين')
            ->icon(Heroicon::OutlinedTableCells)
            ->color('gray')
            ->tooltip('ملف Excel بكل بيانات المسجّلين الظاهرين في الجدول، حسب التبويب والفلاتر والبحث.')
            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
            ->action(function (ListRecords $livewire): StreamedResponse {
                $registrations = $livewire->getFilteredSortedTableQuery()
                    ->with('event')
                    ->get();

                $columns = [
                    'رمز الدعوة' => fn (EventRegistration $r) => $r->invitationCode(),
                    'الاسم الثلاثي' => fn (EventRegistration $r) => $r->name,
                    'رقم الهاتف' => fn (EventRegistration $r) => $r->phone,
                    'رقم الواتساب' => fn (EventRegistration $r) => $r->whatsapp_phone,
                    'البريد الإلكتروني' => fn (EventRegistration $r) => $r->email,
                    'الصفة المهنية أو الأكاديمية' => fn (EventRegistration $r) => $r->professional_title,
                    'يمتلك عيادة' => fn (EventRegistration $r) => $r->owns_clinic === null ? null : ($r->owns_clinic ? 'نعم' : 'لا'),
                    'رقم العضوية' => fn (EventRegistration $r) => $r->membership_number,
                    'مكان العمل' => fn (EventRegistration $r) => $r->workplace,
                    'المحافظة' => fn (EventRegistration $r) => $r->city,
                    'نوع الحضور' => fn (EventRegistration $r) => self::ATTENDANCE_TYPES[$r->attendance_type] ?? $r->attendance_type,
                    'نوع الدعوة' => fn (EventRegistration $r) => $r->is_vip ? 'VIP' : 'عادية',
                    'أُرسلت الدعوة في' => fn (EventRegistration $r) => $r->invitation_sent_at?->format('Y-m-d H:i'),
                    'تمت المعالجة' => fn (EventRegistration $r) => $r->is_handled ? 'نعم' : 'لا',
                    'الفعالية' => fn (EventRegistration $r) => $r->event?->title,
                    'ملاحظات' => fn (EventRegistration $r) => $r->notes,
                    'تاريخ التسجيل' => fn (EventRegistration $r) => $r->created_at?->format('Y-m-d H:i'),
                ];

                $spreadsheet = new Spreadsheet;
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('المسجّلون');
                $sheet->setRightToLeft(true);

                foreach (array_keys($columns) as $index => $heading) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $heading);
                }

                foreach ($registrations as $row => $registration) {
                    foreach (array_values($columns) as $index => $value) {
                        // All as text, so phone numbers keep their leading zero and full length.
                        $sheet->setCellValueExplicit(
                            Coordinate::stringFromColumnIndex($index + 1).($row + 2),
                            (string) $value($registration),
                            DataType::TYPE_STRING,
                        );
                    }
                }

                $lastColumn = Coordinate::stringFromColumnIndex(count($columns));
                $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}".max(1, $registrations->count() + 1));

                foreach (range(1, count($columns)) as $index) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
                }

                return response()->streamDownload(
                    fn () => (new Xlsx($spreadsheet))->save('php://output'),
                    'osif-registrations-'.now()->format('Y-m-d').'.xlsx',
                    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                );
            });
    }
}
