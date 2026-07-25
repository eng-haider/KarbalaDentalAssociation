<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المعاملات', Transaction::count())
                ->description('جميع المعاملات المسجلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('مع عيادة', Transaction::where('transaction_type', 'like', '%عيادة%')->count())
                ->description('تجديد ممارسة مع عيادة')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            Stat::make('بدون عيادة', Transaction::where('transaction_type', 'like', '%بدون عيادة%')->count())
                ->description('تجديد ممارسة بدون عيادة')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),
            Stat::make('طلبات انتماء', Transaction::where('transaction_type', 'like', '%انتماء%')->count())
                ->description('طلبات العضوية الجديدة')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
        ];
    }
}
