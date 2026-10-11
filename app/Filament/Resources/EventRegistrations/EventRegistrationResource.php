<?php

namespace App\Filament\Resources\EventRegistrations;

use App\Filament\Resources\EventRegistrations\Pages\EditEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\Resources\EventRegistrations\Schemas\EventRegistrationForm;
use App\Filament\Resources\EventRegistrations\Tables\EventRegistrationsTable;
use App\Models\EventRegistration;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'الوارد';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'تسجيل';

    protected static ?string $pluralModelLabel = 'تسجيلات الفعاليات';

    public static function form(Schema $schema): Schema
    {
        return EventRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventRegistrationsTable::configure($table);
    }

    public static function getPluralModelLabel(): string
    {
        return static::inOsifPanel() ? 'المسجّلون' : 'تسجيلات الفعاليات';
    }

    public static function getModelLabel(): string
    {
        return static::inOsifPanel() ? 'مسجّل' : 'تسجيل';
    }

    public static function getNavigationGroup(): ?string
    {
        return static::inOsifPanel() ? null : 'الوارد';
    }

    private static function inOsifPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'osif';
    }

    public static function getNavigationBadge(): ?string
    {
        $query = EventRegistration::query()->where('is_handled', false);

        if (Filament::getCurrentPanel()?->getId() === 'osif') {
            $query->whereHas('event', fn ($event) => $event->where('slug', \App\Models\Event::OSIF_SLUG));
        }

        return (string) ($query->count() ?: '');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // The OSIF panel is deliberately narrow: coordinators only see the
        // people registered for this forum, never association submissions.
        if (Filament::getCurrentPanel()?->getId() === 'osif') {
            $query->whereHas('event', fn ($event) => $event->where('slug', \App\Models\Event::OSIF_SLUG));
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventRegistrations::route('/'),
            'edit' => EditEventRegistration::route('/{record}/edit'),
        ];
    }
}
