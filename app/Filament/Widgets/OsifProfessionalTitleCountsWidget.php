<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OsifProfessionalTitleCountsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected ?string $heading = 'المسجّلون حسب الصفة المهنية أو الأكاديمية';

    protected ?string $description = 'عدد التسجيلات في كل صفة، مع إبقاء الخيارات التي لم تُسجّل بعد ظاهرة.';

    protected int|array|null $columns = ['default' => 1, 'sm' => 2, 'xl' => 3];

    protected function getStats(): array
    {
        $event = Event::query()->where('slug', Event::OSIF_SLUG)->first();

        if (! $event) {
            return [];
        }

        $counts = EventRegistration::query()
            ->where('event_id', $event->getKey())
            ->whereNotNull('professional_title')
            ->where('professional_title', '!=', '')
            ->selectRaw('professional_title, COUNT(*) as total')
            ->groupBy('professional_title')
            ->pluck('total', 'professional_title');

        $limits = $event->professionalTitleLimits();

        return collect($event->professionalTitleOptions())
            // Keep a visible count for historic titles that were later removed
            // from the public registration choices.
            ->merge($counts->keys())
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values()
            ->map(function (string $title) use ($counts, $limits): Stat {
                $total = (int) $counts->get($title, 0);
                $limit = $limits[$title] ?? null;

                if ($limit !== null && $total >= $limit) {
                    return Stat::make($title, $total)
                        ->description('اكتمل العدد ('.$limit.')')
                        ->descriptionIcon('heroicon-m-lock-closed')
                        ->color('danger');
                }

                return Stat::make($title, $total)
                    ->description(match (true) {
                        $limit !== null => 'من أصل '.$limit.' · متبقٍ '.($limit - $total),
                        $total > 0 => 'مسجّل بهذه الصفة',
                        default => 'لا توجد تسجيلات بهذه الصفة',
                    })
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color($total ? 'primary' : 'gray');
            })
            ->all();
    }
}
