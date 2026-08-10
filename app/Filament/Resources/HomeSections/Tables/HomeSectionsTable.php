<?php

namespace App\Filament\Resources\HomeSections\Tables;

use App\Models\HomeSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HomeSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('القسم')
                    ->weight('bold')
                    ->description(fn (HomeSection $record): string => $record->key),
                ToggleColumn::make('is_visible')
                    ->label('ظاهر في الصفحة'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->emptyStateHeading('لم تُسجَّل الأقسام بعد')
            ->emptyStateDescription('نفّذ الأمر: php artisan db:seed --class=HomeSectionSeeder --force');
    }
}
