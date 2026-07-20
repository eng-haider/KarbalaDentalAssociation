<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(60)
                    ->weight('bold'),
                IconColumn::make('video_url')
                    ->label('فيديو')
                    ->boolean()
                    ->trueIcon('heroicon-o-play-circle')
                    ->falseIcon('heroicon-o-minus')
                    ->getStateUsing(fn ($record): bool => filled($record->video_url)),
                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')->label('منشور'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
