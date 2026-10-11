<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OsifRegistrationStatsWidget extends BaseWidget
{
    protected static ?int $sort = -2;

    // Two per row keeps the four figures compact on phones.
    protected int|array|null $columns = ['default' => 2, 'lg' => 4];

    protected function getStats(): array
    {
        $registrations = EventRegistration::query()
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG));

        $total = (clone $registrations)->count();
        $sent = (clone $registrations)->whereNotNull('invitation_sent_at')->count();

        return [
            Stat::make('المسجّلون', $total)
                ->description('إجمالي طلبات التسجيل')
                ->descriptionIcon('heroicon-m-users')
                ->chart($this->dailyRegistrations(clone $registrations))
                ->color('primary'),
            Stat::make('أُرسلت الدعوة', $sent)
                ->description($total ? round($sent / $total * 100).'% من المسجّلين' : 'لا توجد تسجيلات بعد')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success'),
            Stat::make('بانتظار الدعوة', $total - $sent)
                ->description('لم تُرسل دعوتهم بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('يمتلكون عيادة', (clone $registrations)->where('owns_clinic', true)->count())
                ->description('من إجابات نموذج التسجيل')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),
        ];
    }

    /** @return list<int> */
    private function dailyRegistrations($registrations): array
    {
        $since = now()->subDays(13)->startOfDay();

        $counts = $registrations
            ->where('created_at', '>=', $since)
            ->pluck('created_at')
            ->countBy(fn ($createdAt) => $createdAt->toDateString());

        return collect(range(13, 0))
            ->map(fn (int $daysAgo) => $counts->get(now()->subDays($daysAgo)->toDateString(), 0))
            ->all();
    }
}
