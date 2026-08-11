<?php

namespace App\Filament\Resources\JobOpenings;

use App\Filament\Resources\JobOpenings\Pages\CreateJobOpening;
use App\Filament\Resources\JobOpenings\Pages\EditJobOpening;
use App\Filament\Resources\JobOpenings\Pages\ListJobOpenings;
use App\Filament\Resources\JobOpenings\Schemas\JobOpeningForm;
use App\Filament\Resources\JobOpenings\Tables\JobOpeningsTable;
use App\Models\JobOpening;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JobOpeningResource extends Resource
{
    protected static ?string $model = JobOpening::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'فرصة عمل';

    protected static ?string $pluralModelLabel = 'فرص العمل';

    public static function form(Schema $schema): Schema
    {
        return JobOpeningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobOpeningsTable::configure($table);
    }

    /** Badge counts what the public can actually see right now. */
    public static function getNavigationBadge(): ?string
    {
        return (string) (JobOpening::open()->count() ?: '');
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
            'index' => ListJobOpenings::route('/'),
            'create' => CreateJobOpening::route('/create'),
            'edit' => EditJobOpening::route('/{record}/edit'),
        ];
    }
}
