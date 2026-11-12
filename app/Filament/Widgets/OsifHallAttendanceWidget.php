<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\OsifCheckIn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Conference day: how many guests entered each hall, and how many each scanner read. */
class OsifHallAttendanceWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected ?string $heading = 'حضور القاعات (مسح QR)';

    protected int|array|null $columns = ['default' => 1, 'md' => 3];

    protected function getStats(): array
    {
        $entered = EventRegistration::query()
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->whereNotNull('checked_in_at')
            ->count();

        // Every hall and scanner count in one query.
        $counts = OsifCheckIn::query()
            ->leftJoin('users', 'users.id', '=', 'osif_check_ins.user_id')
            ->groupBy('osif_check_ins.hall', 'osif_check_ins.user_id', 'users.name')
            ->selectRaw('osif_check_ins.hall, users.name, count(*) as scans')
            ->toBase()
            ->get()
            ->groupBy('hall');

        $stats = [
            Stat::make('دخلوا المؤتمر', $entered)
                ->description('ضيوف دخلوا قاعة واحدة على الأقل')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
        ];

        foreach (OsifCheckIn::HALLS as $hall => $name) {
            $scanners = $counts->get($hall, collect())->sortByDesc('scans');

            $stats[] = Stat::make($name, $scanners->sum('scans'))
                ->description($scanners->isEmpty()
                    ? 'لم تُقرأ أي بطاقة بعد'
                    : $scanners->map(fn ($row) => ($row->name ?? 'حساب محذوف').': '.$row->scans)->join(' · '))
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('primary');
        }

        return $stats;
    }
}
