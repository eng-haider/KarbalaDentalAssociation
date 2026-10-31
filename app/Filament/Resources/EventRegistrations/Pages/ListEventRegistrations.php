<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Resources\EventRegistrations\Actions\ExportNumbersAction;
use App\Filament\Resources\EventRegistrations\Actions\ExportRegistrationsAction;
use App\Filament\Resources\EventRegistrations\Actions\ReturnUndeliveredInvitationsAction;
use App\Filament\Resources\EventRegistrations\Actions\SendAllInvitationsAction;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Widgets\OsifProfessionalTitleCountsWidget;
use App\Filament\Widgets\OsifRegistrationStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListEventRegistrations extends ListRecords
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendAllInvitationsAction::make(),
            ReturnUndeliveredInvitationsAction::make(),
            ExportNumbersAction::make(),
            ExportRegistrationsAction::make(),
            CreateAction::make()
                ->label('إضافة تسجيل')
                ->icon(Heroicon::OutlinedUserPlus),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return $this->inOsifPanel()
            ? [OsifRegistrationStatsWidget::class, OsifProfessionalTitleCountsWidget::class]
            : [];
    }

    public function getTabs(): array
    {
        if (! $this->inOsifPanel()) {
            return [];
        }

        $count = fn (?callable $scope = null): int => EventRegistrationResource::getEloquentQuery()
            ->when($scope, $scope)
            ->count();

        return [
            'all' => Tab::make('الكل')
                ->badge($count()),
            'pending' => Tab::make('بانتظار الدعوة')
                ->icon(Heroicon::OutlinedClock)
                ->badge($count(fn (Builder $query) => $query->whereNull('invitation_sent_at')))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('invitation_sent_at')),
            'sent' => Tab::make('أُرسلت الدعوة')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->badge($count(fn (Builder $query) => $query->whereNotNull('invitation_sent_at')))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('invitation_sent_at')),
        ];
    }

    private function inOsifPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'osif';
    }
}
