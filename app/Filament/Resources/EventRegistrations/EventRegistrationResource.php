<?php

namespace App\Filament\Resources\EventRegistrations;

use App\Filament\Resources\EventRegistrations\Pages\EditEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\Resources\EventRegistrations\Schemas\EventRegistrationForm;
use App\Filament\Resources\EventRegistrations\Tables\EventRegistrationsTable;
use App\Models\EventRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
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

    public static function getNavigationBadge(): ?string
    {
        return (string) (EventRegistration::where('is_handled', false)->count() ?: '');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
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
